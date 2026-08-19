<?php

namespace App\Support;

use Composer\InstalledVersions;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class PackageVersionService
{
    public static function getComposerJson(): array
    {
        $path = base_path('composer.json');

        if (! file_exists($path)) {
            return [];
        }

        $json = json_decode(file_get_contents($path), true);

        return is_array($json) ? $json : [];
    }

    public static function composerJsonRequirements(): array
    {
        $json = self::getComposerJson();

        return $json['require'] ?? [];
    }

    public static function composerJsonDevRequirements(): array
    {
        $json = self::getComposerJson();

        return $json['require-dev'] ?? [];
    }

    public static function getOutdatedPayload(): array
    {
        $file = storage_path('app/composer-outdated.json');

        if (! file_exists($file)) {
            return [
                'last_checked' => null,
                'installed' => [],
            ];
        }

        $json = json_decode(file_get_contents($file), true);

        if (! is_array($json)) {
            return [
                'last_checked' => null,
                'installed' => [],
            ];
        }

        return [
            'last_checked' => $json['last_checked'] ?? null,
            'installed' => collect($json['installed'] ?? [])
                ->keyBy('name')
                ->toArray(),
        ];
    }

    public static function refreshOutdatedScan(): void
    {
        $process = new Process(['composer', 'outdated', '--direct', '--format=json'], base_path());
        $process->setTimeout(120);
        $process->run();

        if (! $process->isSuccessful() && trim($process->getOutput()) === '') {
            throw new \RuntimeException(trim($process->getErrorOutput()) ?: 'Composer outdated scan failed.');
        }

        $payload = json_decode($process->getOutput(), true);

        if (! is_array($payload)) {
            throw new \RuntimeException('Composer outdated scan returned invalid JSON.');
        }

        $payload['last_checked'] = now()->toDateTimeString();

        File::ensureDirectoryExists(storage_path('app'));
        File::put(
            storage_path('app/composer-outdated.json'),
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL,
        );
    }

    public static function getLastChecked(): ?string
    {
        return self::getOutdatedPayload()['last_checked'];
    }

    // public static function getPackageInfo(string $package, string $type = 'require'): array
    // {
    //     $requirements = self::composerJsonRequirements();
    //     $devRequirements = self::composerJsonDevRequirements();
    //     $outdated = self::getOutdatedPayload()['installed'];

    //     $installed = InstalledVersions::isInstalled($package);
    //     $outdatedInfo = $outdated[$package] ?? null;

    //     $constraint = match ($type) {
    //         'require-dev' => $devRequirements[$package] ?? null,
    //         default => $requirements[$package] ?? null,
    //     };

    //     $status = 'Instalado';

    //     if (! $installed) {
    //         $status = 'Não instalado';
    //     } elseif ($outdatedInfo) {
    //         $status = 'Update disponível';
    //     }



    //     return [
    //         'name' => $package,
    //         'type' => $type,
    //         'installed' => $installed,
    //         'version' => $installed ? InstalledVersions::getPrettyVersion($package) : null,
    //         'reference' => $installed ? InstalledVersions::getReference($package) : null,
    //         'constraint' => $constraint,
    //         'status' => $status,
    //         'latest' => $outdatedInfo['latest'] ?? null,
    //         'latest_status' => $outdatedInfo['latest-status'] ?? null,
    //         'description' => $outdatedInfo['description'] ?? null,
    //     ];
    // }


    public static function getPackageInfo(string $package, string $type = 'require'): array
    {
        $requirements = self::composerJsonRequirements();
        $devRequirements = self::composerJsonDevRequirements();
        $outdated = self::getOutdatedPayload()['installed'];

        $installed = InstalledVersions::isInstalled($package);
        $outdatedInfo = $outdated[$package] ?? null;

        $currentVersion = $installed
            ? InstalledVersions::getPrettyVersion($package)
            : null;

        $latestVersion = $outdatedInfo['latest'] ?? null;

        $constraint = match ($type) {
            'require-dev' => $devRequirements[$package] ?? null,
            default => $requirements[$package] ?? null,
        };

        $updateType = self::detectUpdateType($currentVersion, $latestVersion);

        $status = 'Instalado';

        if (! $installed) {
            $status = 'Não instalado';
        } elseif ($outdatedInfo) {
            $status = $updateType === 'major'
                ? 'Update major'
                : 'Update minor/patch';
        }

        return [
            'name' => $package,
            'type' => $type,
            'installed' => $installed,
            'version' => $currentVersion,
            'reference' => $installed ? InstalledVersions::getReference($package) : null,
            'constraint' => $constraint,
            'status' => $status,
            'update_type' => $updateType,
            'latest' => $latestVersion,
            'latest_status' => $outdatedInfo['latest-status'] ?? null,
            'description' => $outdatedInfo['description'] ?? null,
        ];
    }


    public static function getDirectPackages(): array
    {
        return collect(array_keys(self::composerJsonRequirements()))
            ->reject(fn(string $package) => $package === 'php')
            ->map(fn(string $package) => self::getPackageInfo($package, 'require'))
            ->sortBy('name')
            ->values()
            ->all();
    }

    public static function getDevPackages(): array
    {
        return collect(array_keys(self::composerJsonDevRequirements()))
            ->map(fn(string $package) => self::getPackageInfo($package, 'require-dev'))
            ->sortBy('name')
            ->values()
            ->all();
    }

    public static function getOutdatedPackages(): array
    {
        return collect(self::getDirectPackages())
            ->merge(self::getDevPackages())
            ->filter(fn(array $package) => in_array($package['status'], ['Update major', 'Update minor/patch'], true))
            ->sortBy('name')
            ->values()
            ->all();
    }

    public static function getStats(): array
    {
        $direct = self::getDirectPackages();
        $dev = self::getDevPackages();
        $outdated = self::getOutdatedPackages();

        return [
            'direct_count' => count($direct),
            'dev_count' => count($dev),
            'outdated_count' => count($outdated),
        ];
    }

    protected static function extractMajor(?string $version): ?int
    {
        if (! $version) {
            return null;
        }

        $version = ltrim($version, 'vV');

        preg_match('/^(\d+)/', $version, $matches);

        return isset($matches[1]) ? (int) $matches[1] : null;
    }

    protected static function detectUpdateType(?string $current, ?string $latest): ?string
    {
        if (! $current || ! $latest) {
            return null;
        }

        $currentMajor = self::extractMajor($current);
        $latestMajor = self::extractMajor($latest);

        if ($currentMajor === null || $latestMajor === null) {
            return null;
        }

        if ($latestMajor > $currentMajor) {
            return 'major';
        }

        if ($latest !== $current) {
            return 'minor/patch';
        }

        return 'none';
    }
}
