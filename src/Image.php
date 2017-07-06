<?php
/**
 * 图像处理
 *
 * @version 1.0
 * @author lshorz
 */

namespace Lshorz\Imagelib;

use Lshorz\Imagelib\ImageCommon;
use Lshorz\Imagelib\GifCommon;
use Lshorz\Imagelib\Gif\GifFrameExtractor;


class Image
{
    /**
     * 初始化要处理的图像
     *
     * @param mixed $file
     *
     * @return ImageCommon|GifCommon
     */
    public static function open($file)
    {
        if (is_string($file) && GifFrameExtractor::isAnimatedGif($file)) {
            $object = new GifCommon();
            return $object->open($file);
        } else {
            $object = new ImageCommon();
            return $object->open($file);
        }
    }
}