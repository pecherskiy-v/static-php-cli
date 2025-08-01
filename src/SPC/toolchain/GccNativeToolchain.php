<?php

declare(strict_types=1);

namespace SPC\toolchain;

use SPC\builder\freebsd\SystemUtil as FreeBSDSystemUtil;
use SPC\builder\linux\SystemUtil as LinuxSystemUtil;
use SPC\builder\macos\SystemUtil as MacOSSystemUtil;
use SPC\exception\WrongUsageException;
use SPC\util\GlobalEnvManager;

class GccNativeToolchain implements ToolchainInterface
{
    public function initEnv(): void
    {
        GlobalEnvManager::putenv('SPC_LINUX_DEFAULT_CC=gcc');
        GlobalEnvManager::putenv('SPC_LINUX_DEFAULT_CXX=g++');
        GlobalEnvManager::putenv('SPC_LINUX_DEFAULT_AR=ar');
        GlobalEnvManager::putenv('SPC_LINUX_DEFAULT_LD=ld');
    }

    public function afterInit(): void
    {
        foreach (['CC', 'CXX', 'AR', 'LD'] as $env) {
            $command = getenv($env);
            if (!$command || is_file($command)) {
                continue;
            }
            match (PHP_OS_FAMILY) {
                'Linux' => LinuxSystemUtil::findCommand($command) ?? throw new WrongUsageException("{$command} not found, please install it or set {$env} to a valid path."),
                'Darwin' => MacOSSystemUtil::findCommand($command) ?? throw new WrongUsageException("{$command} not found, please install it or set {$env} to a valid path."),
                'BSD' => FreeBSDSystemUtil::findCommand($command) ?? throw new WrongUsageException("{$command} not found, please install it or set {$env} to a valid path."),
                default => throw new \RuntimeException(__CLASS__ . ' is not supported on ' . PHP_OS_FAMILY . '.'),
            };
        }
    }
}
