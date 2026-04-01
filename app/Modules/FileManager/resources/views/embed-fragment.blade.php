@include('file-manager::partials.embed-root', [
    'standalone' => false,
    'compact' => true,
    'basePath' => $basePath,
    'initialFilePath' => $initialFilePath,
    'requestedPath' => $requestedPath,
    'initialMissingTarget' => $initialMissingTarget,
    'directoryTarget' => $directoryTarget,
    'targetInfo' => $targetInfo,
])
