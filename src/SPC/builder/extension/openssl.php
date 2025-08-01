<?php

declare(strict_types=1);

namespace SPC\builder\extension;

use SPC\builder\Extension;
use SPC\util\CustomExt;

#[CustomExt('openssl')]
class openssl extends Extension
{
    public function patchBeforeMake(): bool
    {
        $patched = parent::patchBeforeMake();
        // patch openssl3 with php8.0 bug
        if ($this->builder->getPHPVersionID() < 80100) {
            $openssl_c = file_get_contents(SOURCE_PATH . '/php-src/ext/openssl/openssl.c');
            $openssl_c = preg_replace('/REGISTER_LONG_CONSTANT\s*\(\s*"OPENSSL_SSLV23_PADDING"\s*.+;/', '', $openssl_c);
            file_put_contents(SOURCE_PATH . '/php-src/ext/openssl/openssl.c', $openssl_c);
            return true;
        }

        return $patched;
    }

    public function getUnixConfigureArg(bool $shared = false): string
    {
        $openssl_dir = $this->builder->getPHPVersionID() >= 80400 ? '' : ' --with-openssl-dir=' . BUILD_ROOT_PATH;
        return '--with-openssl=' . ($shared ? 'shared,' : '') . BUILD_ROOT_PATH . $openssl_dir;
    }
}
