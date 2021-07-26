<?php

namespace Mix\Vega;

use Mix\Vega\Exception\NotFoundException;

/**
 * Trait StaticFile
 * @package Mix\Vega
 */
trait StaticFile
{

    /**
     * @param string $path
     * @param string $root
     */
    public function static(string $path, string $root)
    {
        if (substr($path, -1, 1) == '/') {
            $path = substr($path, 0, -1);
        }
        $pattern = $path . '/{any:.+}';
        $this->handleFunc($pattern, function (Context $ctx) use ($root, $path) {
            $uriPath = $ctx->uri()->getPath();
            $file = sprintf('%s%s', $root, substr($uriPath, strlen($path)));
            if (!file_exists($file)) {
                throw new NotFoundException('404 Not Found', 404);
            }

            // 防止相对路径攻击
            // 如：/static/../../foo.php
            $absFile = (string)realpath($file);
            $absRoot = realpath($root);
            if (!$absRoot || $absRoot !== substr($absFile, 0, strlen($absRoot))) {
                throw new NotFoundException('404 Not Found', 404);
            }

            if (static::ifModifiedSince($ctx, $absFile)) {
                $ctx->status(304);
                $ctx->response->send();
                return;
            }
            $ctx->response->sendfile($absFile);
        })->methods('GET');
    }

    /**
     * @param string $path
     * @param string $file
     */
    public function staticFile(string $path, string $file)
    {
        $this->handleFunc($path, function (Context $ctx) use ($file) {
            if (!file_exists($file)) {
                throw new NotFoundException('404 Not Found', 404);
            }

            if (static::ifModifiedSince($ctx, $file)) {
                $ctx->status(304);
                $ctx->response->send();
                return;
            }
            $ctx->response->sendfile($file);
        })->methods('GET');
    }

    /**
     * @param Context $ctx
     * @param string $file
     * @return bool
     */
    protected static function ifModifiedSince(Context $ctx, string $file): bool
    {
        $ifModifiedSince = $ctx->header('if-modified-since');
        if (empty($ifModifiedSince) || !($mtime = filemtime($file))) {
            return false;
        }
        return $ifModifiedSince === gmdate('D, d M Y H:i:s', $mtime) . ' GMT';
    }

}
