using System;
using System.Text;
using System.ComponentModel;
using System.Runtime.InteropServices;

// Bounded console launch and the native Apache WinNT shutdown event.
// No process memory writes, alternate token, service, dialog input or error suppression.
public static class GramlyzeStartupNative {
    [StructLayout(LayoutKind.Sequential, CharSet = CharSet.Unicode)]
    private struct StartupInfo {
        public int cb; public string reserved, desktop, title;
        public uint x, y, xSize, ySize, xChars, yChars, fill, flags;
        public ushort showWindow, reservedSize;
        public IntPtr reservedBytes, input, output, error;
    }
    [StructLayout(LayoutKind.Sequential)]
    private struct ProcessInfo { public IntPtr process, thread; public uint pid, tid; }
    [DllImport("kernel32.dll")] public static extern uint GetErrorMode();
    [DllImport("kernel32.dll", CharSet = CharSet.Unicode, SetLastError = true)]
    private static extern bool CreateProcessW(string application, StringBuilder command,
        IntPtr processAttributes, IntPtr threadAttributes, bool inheritHandles,
        uint flags, IntPtr environment, string directory, ref StartupInfo startup,
        out ProcessInfo process);
    [DllImport("kernel32.dll", CharSet = CharSet.Unicode, SetLastError = true)]
    private static extern IntPtr OpenEventW(uint access, bool inherit, string name);
    [DllImport("kernel32.dll", SetLastError = true)] private static extern bool SetEvent(IntPtr handle);
    [DllImport("kernel32.dll")] private static extern bool CloseHandle(IntPtr handle);
    [DllImport("kernel32.dll", SetLastError = true)] private static extern IntPtr OpenProcess(uint access, bool inherit, uint pid);
    [DllImport("kernel32.dll", SetLastError = true)] private static extern bool GetProcessTimes(IntPtr process, out long created, out long exited, out long kernel, out long user);
    [DllImport("kernel32.dll")] private static extern uint WaitForSingleObject(IntPtr handle, uint milliseconds);
    [DllImport("kernel32.dll")] private static extern IntPtr GetCurrentProcess();
    [DllImport("advapi32.dll", SetLastError = true)] private static extern bool OpenProcessToken(IntPtr process, uint access, out IntPtr token);
    [DllImport("userenv.dll", SetLastError = true)] private static extern bool CreateEnvironmentBlock(out IntPtr environment, IntPtr token, bool inherit);
    [DllImport("userenv.dll")] private static extern bool DestroyEnvironmentBlock(IntPtr environment);

    public static uint StartConsole(string executable, string command, string directory) {
        var startup = new StartupInfo { cb = Marshal.SizeOf(typeof(StartupInfo)) };
        ProcessInfo process;
        // CREATE_NEW_CONSOLE | CREATE_DEFAULT_ERROR_MODE | CREATE_UNICODE_ENVIRONMENT:
        // visible ordinary console,
        // with system-default error mode instead of the agent launcher's inherited mode.
        // https://learn.microsoft.com/en-us/windows/win32/procthread/process-creation-flags
        // No stdio redirection, CREATE_NO_WINDOW, debug flags, or SetErrorMode call.
        IntPtr token, environment = IntPtr.Zero;
        if (!OpenProcessToken(GetCurrentProcess(), 0x000A, out token))
            throw new Win32Exception(Marshal.GetLastWin32Error());
        try {
            // Reconstruct this signed-in user's normal environment from Windows,
            // rather than inheriting agent-added PATH entries. No global changes.
            if (!CreateEnvironmentBlock(out environment, token, false))
                throw new Win32Exception(Marshal.GetLastWin32Error());
            if (!CreateProcessW(executable, new StringBuilder(command), IntPtr.Zero,
                IntPtr.Zero, false, 0x04000410, environment, directory, ref startup, out process))
                throw new Win32Exception(Marshal.GetLastWin32Error());
            try { return process.pid; }
            finally { CloseHandle(process.thread); CloseHandle(process.process); }
        } finally {
            if (environment != IntPtr.Zero) DestroyEnvironmentBlock(environment);
            CloseHandle(token);
        }
    }

    public static long CreationFileTime(uint pid) {
        IntPtr process = OpenProcess(0x00101000, false, pid);
        if (process == IntPtr.Zero) throw new Win32Exception(Marshal.GetLastWin32Error());
        try {
            long created, exited, kernel, user;
            if (!GetProcessTimes(process, out created, out exited, out kernel, out user))
                throw new Win32Exception(Marshal.GetLastWin32Error());
            if (WaitForSingleObject(process, 0) != 258) throw new InvalidOperationException("Apache process no longer alive");
            return created;
        } finally { CloseHandle(process); }
    }

    public static void Shutdown(uint parentPid, long expectedCreationFileTime) {
        // Apache 2.4.58 server/mpm/winnt/mpm_winnt.c: apPID_shutdown.
        // Open the existing event; never create one or signal all processes by name.
        IntPtr process = OpenProcess(0x00101000, false, parentPid);
        if (process == IntPtr.Zero) throw new Win32Exception(Marshal.GetLastWin32Error());
        try {
            long created, exited, kernel, user;
            if (!GetProcessTimes(process, out created, out exited, out kernel, out user))
                throw new Win32Exception(Marshal.GetLastWin32Error());
            if (created != expectedCreationFileTime || WaitForSingleObject(process, 0) != 258)
                throw new InvalidOperationException("Apache process identity or liveness changed");
            IntPtr handle = OpenEventW(0x0002, false, "ap" + parentPid + "_shutdown");
            if (handle == IntPtr.Zero) throw new Win32Exception(Marshal.GetLastWin32Error());
            try {
                if (WaitForSingleObject(process, 0) != 258) throw new InvalidOperationException("Apache exited before shutdown signal");
                if (!SetEvent(handle)) throw new Win32Exception(Marshal.GetLastWin32Error());
            } finally { CloseHandle(handle); }
        } finally { CloseHandle(process); }
    }
}
