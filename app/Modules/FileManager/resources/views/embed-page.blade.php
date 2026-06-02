<!DOCTYPE html>
<html lang="uk" style="height:100%;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>File Editor Embed</title>
    <script defer src="{{ route('file-manager.embed.bootstrap') }}"></script>
</head>
<body style="margin:0;min-height:100vh;min-height:100dvh;height:100vh;height:100dvh;background:#f8fafc;color:#0f172a;font-family:ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;">
    @include('file-manager::partials.embed-root', [
        'standalone' => true,
        'compact' => false,
        'basePath' => $basePath,
        'initialFilePath' => $initialFilePath,
        'requestedPath' => $requestedPath,
        'initialMissingTarget' => $initialMissingTarget,
        'directoryTarget' => $directoryTarget,
        'targetInfo' => $targetInfo,
    ])
</body>
</html>
