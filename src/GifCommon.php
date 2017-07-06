<?php

namespace Lshorz\Imagelib;

use Lshorz\Imagelib\Gif\GifFrameExtractor;
use Lshorz\Imagelib\Gif\GifCreator;

class GifCommon extends AbstractImage
{
    /**
     * 生成image图像对象数组
     *
     * @param string $filePath
     *
     * @return $this
     */
    public function open($filePath = null)
    {
        $gif = new GifFrameExtractor();
        $gif->extract($filePath);
        $this->frames = $gif->getFrames();

        $this->image = $this->imageManager->make($filePath);

        $this->gif = array_map(function ($res) {
            return $this->imageManager->make($res['image']);
        }, $this->frames);

        return $this;
    }

    /**
     * 生成缩略图
     *
     * @param int   $width
     * @param int   $height
     * @param mixed $background  背景色
     *                           [null:透明需png支持, '#00ff00', 'rgba(0, 0, 0, 0.5)', array(255,255,255, 0.5)]
     * @param int   $type        缩略图处理类型
     * @param int   $position    定位类型
     *
     * @return $this
     */
    public function thumb($width = null, $height = null, $background = null, $type = self::THUMB_AUTO, $position = null)
    {
        // TODO: Implement resize() method.

        $position = $this->getPosition($position);

        switch ($type) {
            //1自动计算等比例缩放
            case self::THUMB_AUTO :
                $this->gif = array_map(function ($image) use ($width, $height) {
                    return $image->resize($width, $height, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                }, $this->gif);
                break;
            //2缩略图自动缩放并裁剪
            case self::THUMB_FIT :
                $this->gif = array_map(function ($image) use ($width, $height, $position) {
                    return $image->fit($width, $height, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    }, $position['pos']);
                }, $this->gif);
                break;
            //3缩略图保持原图比例缩放后填充
            case self::THUMB_FILL_RESIZE :
                $this->gif = $this->resizeExtend($width, $height, $background);
                break;
            //4缩略图按比例占满填充
            case self::THUMB_FILL_SCALE_RESIZE :
                $this->gif = $this->scaleResize($width, $height, $background);
                break;
            //5缩略图固定缩放占满
            case self::THUMB_FORCE_RESIZE :
                $this->gif = array_map(function ($image) use ($width, $height) {
                    return $image->resize($width, $height, function ($constraint) {
                        $constraint->upsize();
                    });
                }, $this->gif);
                break;
            default:
                $this->gif = [];
                break;
        }
        return $this;
    }

    /**
     * 创建水印
     *
     * @param mixed $waterImg 水印文件
     * @param int   $position 水印位置
     * @param int   $alpha    水印图片透明度
     *
     * @return $this
     */
    public function waterMark($waterImg, $position = self::POS_BOTTOM_LEFT, $alpha = 0)
    {
        // TODO: Implement resize() method.

        $position = $this->getPosition($position);

        $waterImg = $this->imageManager->make($waterImg);

        if ($alpha > 0) {
            $waterImg->opacity($alpha);
        }

        $this->gif = array_map(function ($image) use ($waterImg, $position) {
            return $image->insert($waterImg, $position['pos'], 5, 5);
        }, $this->gif);

        $waterImg->destroy();

        return $this;
    }

    /**
     * 创建字体水印
     *
     * @param string $text       文字内容
     * @param int    $size       字体大小
     * @param mixed  $color      字体颜色
     *                           [null:透明需png支持, '#00ff00', 'rgba(0, 0, 0, 0.5)',
     * @param int    $position   字体定位
     * @param int    $angle      旋转角度
     * @param string $fontType   字体路径
     *
     * @return $this
     */
    public function text($text, $size = 24, $color = '#000000', $position = self::POS_TOP_LEFT, $angle = 0, $fontType = null)
    {
        // TODO: Implement resize() method.

        $position = $this->getPosition($position);
        $info = $this->getInfo();

        $fontType = $fontType ? $fontType : __DIR__ . '/../assets/fonts/arialbi.ttf';

        //创建字体
        $fontclassname = sprintf('\Intervention\Image\%s\Font',
            $this->image->getDriver()->getDriverName());

        $font = new $fontclassname($text);
        $font->file($fontType);
        $font->size($size);
        $font->align('left');
        $font->valign('top');

        //获得字体框宽高
        $fontBoxSize = $font->getBoxSize();

        //计算文字在图片上的显示位置
        switch ($position['pos']) {
            case 'top-left':
                $x = $y = 5;
                break;
            case 'top' :
                $x = ($info['width'] - $fontBoxSize['width']) / 2;
                $y = 5;
                break;
            case 'top-right':
                $x = $info['width'] - $fontBoxSize['width'] - 5;
                $y = 5;
                break;
            case 'left' :
                $x = 5;
                $y = ($info['height'] - $fontBoxSize['height']) / 2;
                break;
            case 'right' :
                $x = $info['width'] - $fontBoxSize['width'] - 5;
                $y = ($info['height'] - $fontBoxSize['height']) / 2;
                break;
            case 'bottom-left':
                $x = 5;
                $y = $info['height'] - $fontBoxSize['height'] - 5;
                break;
            case 'bottom' :
                $x = ($info['width'] - $fontBoxSize['width']) / 2;
                $y = $info['height'] - $fontBoxSize['height'] - 5;
                break;
            case 'bottom-right' :
                $x = $info['width'] - $fontBoxSize['width'] - 5;
                $y = $info['height'] - $fontBoxSize['height'] - 5;
                break;
            case 'center' :
                $x = ($info['width'] - $fontBoxSize['width']) / 2;
                $y = ($info['height'] - $fontBoxSize['height']) / 2;
                break;
            default :
                $x = $y = 5;
        }


        foreach ($this->gif as $key => $image) {
            $this->gif[$key] = $image->text($text, $x, $y, function ($font) use ($size, $color, $fontType, $angle) {
                $font->file($fontType);
                $font->size($size);
                $font->color($color);
                $font->align('left');
                $font->valign('top');
                $font->angle($angle);
            });
        }

        return $this;
    }

    /**
     * 保存图像
     *
     * @param string $path
     * @param int    $quality 图像质量
     *
     * @return boolean
     */
    public function save($path, $quality = null)
    {
        // TODO: Implement resize() method.

        $gifBinary = $this->makeGif();
        $saved = @file_put_contents($path, $gifBinary);

        unset($this->gif);
        return $saved;
    }

    /**
     * 生成图像http响应
     *
     * @return mixed
     */
    public function response($format = 'gif', $quality = 90)
    {
        // TODO: Implement resize() method.

        $format = strtolower($format);
        $gifBinary = $this->makeGif();

        unset($this->gif);

        switch ($format) {
            case 'gif':
            case 'image/gif':
                $length = strlen($gifBinary);
                header('Content-type: image/gif');
                header('Content-Length: ' . $length);
                echo $gifBinary;
                break;
            default:
                $image = $this->imageManager->make($gifBinary);
                $response = $image->response($format, $quality);
                $image->destroy();
                return $response;
                break;

        }
        return true;
    }

    /**
     * 以特定图像格式生成相关编码
     *
     * @param string $format  图像格式[jpg|png|gif|tip|bmp|data-url]
     * @param int    $quality 图像质量
     *
     * @return mixed
     */
    public function encode($format = 'gif', $quality = 90)
    {
        // TODO: Implement resize() method.

        $encode = null;
        $format = strtolower($format);
        $code = $this->makeGif();

        switch ($format) {
            case 'gif':
            case 'image/gif':
                $encode = $code;
                break;
            case 'data-url' :
                $encode = sprintf('data:%s;base64,%s',
                    'image/gif',
                    base64_encode($code)
                );
                break;
            default:
                $image = $this->imageManager->make($code);
                $encode = $image->encode($format, $quality);
                break;

        }

        unset($this->gif);

        return $encode;
    }

    /**
     * 获取图像信息
     *
     * @return array
     */
    public function getInfo()
    {
        // TODO: Implement resize() method.
        $this->info = [
            'width' => $this->image->width(),
            'height' => $this->image->height(),
            'mime' => $this->image->mime(),
            'filesize' => $this->image->filesize(),
        ];

        $this->image->destroy();
        return $this->info;
    }

    /**
     * 图像大小
     *
     * @param int  $width
     * @param int  $height
     * @param bool $ratio 自动计算比例
     *
     * @return $this
     */
    public function resize($width = null, $height = null, $ratio = false)
    {
        // TODO: Implement resize() method.

        foreach ($this->gif as $key => $image) {
            $this->gif[$key] = $image->resize($width, $height, function ($constraint) use ($ratio) {
                if ($ratio) {
                    $constraint->aspectRatio();
                }
                $constraint->upsize();
            });
        }

        return $this;
    }

    /**
     * 图像裁剪
     *
     * @param int $width  裁剪宽度
     * @param int $height 裁剪高度
     * @param int $x
     * @param int $y
     *
     * @return $this
     */
    public function crop($width, $height, $x, $y)
    {
        // TODO: Implement resize() method.

        $width = round($width);
        $height = round($height);
        $x = round($x);
        $y = round($y);
        $this->gif = array_map(function($image) use ($width, $height, $x, $y){
            return $image->crop($width, $height, $x, $y);
        }, $this->gif);

        return $this;
    }

    /**
     * 图像旋转
     *
     * @param float $angle       (-90 顺时针, 90逆时针)
     * @param mixed $background  背景色
     *                           [null:透明需png支持, '#00ff00', 'rgba(0, 0, 0, 0.5)', array(255,255,255, 0.5)]
     *
     * @return $this
     */
    public function rotate($angle, $background = null)
    {
        $this->gif = array_map(function($image) use ($angle, $background){
            return $image->rotate($angle, $background);
        }, $this->gif);

        return $this;
    }

    /**
     * 翻转图像
     *
     * @param int $model
     *
     * @return $this
     */
    public function flip($model = self::FLIP_H)
    {
        // TODO: Implement filp() method.
        $this->gif = array_map(function($image) use ($model){
            return $image->flip($model);
        }, $this->gif);

        return $this;
    }

    /**
     * 将图像转成灰度
     *
     * @return $this
     */
    public function greyscale()
    {
        // TODO: Implement greyscale() method.
        $this->gif = array_map(function($image){
            return $image->greyscale();
        }, $this->gif);

        return $this;
    }

    /**
     * 将图像像素化
     *
     * @param int $size
     */
    public function pixelate($size)
    {
        // TODO: Implement pixelate() method.
        $this->gif = array_map(function($image) use ($size){
            return $image->pixelate($size);
        }, $this->gif);

        return $this;
    }

    /**
     * 按比例缩放大小，不足的地方以背景填充
     *
     * @return array
     */
    protected function scaleResize($width = null, $height = null, $background = null, $crop = false)
    {
        return $this->resizeExtend($width, $height, $background, false, true, $crop);
    }

    /**
     * 强制缩放成固定大小
     *
     * @return array
     */
    protected function forceResize($width = null, $height = null, $background = null)
    {
        return $this->resizeExtend($width, $height, $background, true);
    }

    /**
     * 缩放大小扩展方式，不足的地方以背景填充
     *
     * @return array
     */
    protected function resizeExtend($width = null, $height = null, $background = null, $force = false, $rescale = false, $crop = false)
    {
        $arr = [];
        foreach ($this->gif as $key => $image) {
            $current_width = $image->width();    //源图宽
            $current_height = $image->height();  //源图高
            $new_width = 0;  //缩略图宽
            $new_height = 0; //缩略图高
            $scale = 1.0;    //缩放比例

            if ($height === null && preg_match('#^(.+)%$#mUsi', $width, $matches)) {
                $width = round($current_width * ((float)$matches[1] / 100.0));
                $height = round($current_height * ((float)$matches[1] / 100.0));
            }

            if (!$rescale && (!$force || $crop)) {
                if ($width != null && $current_width > $width) {
                    $scale = $current_width / $width;
                }

                if ($height != null && $current_height > $height) {
                    if ($current_height / $height > $scale) {
                        $scale = $current_height / $height;
                    }
                }
            } else {
                if ($width != null) {
                    $scale = $current_width / $width;
                    $new_width = $width;
                }

                if ($height != null) {
                    if ($width != null && $rescale) {
                        $scale = max($scale, $current_height / $height);
                    } else {
                        $scale = $current_height / $height;
                    }
                    $new_height = $height;
                }
            }

            if (!$force || $width == null || $rescale) {
                $new_width = round($current_width / $scale);
            }

            if (!$force || $height == null || $rescale) {
                $new_height = round($current_height / $scale);
            }

            if ($width == null || $crop) {
                $width = $new_width;
            }

            if ($height == null || $crop) {
                $height = $new_height;
            }

            $arr[$key] = $this->doResizeExtend($image, $background, $width, $height, $new_width, $new_height);
        }


        return $arr;
    }

    /**
     * 执行扩展缩放
     *
     * @param Image $image
     * @param mixed $background    背景色
     *                             [null:透明需png支持, '#00ff00', 'rgba(0, 0, 0, 0.5)', array(255,255,255, 0.5)]
     * @param int   $target_width  画布宽度
     * @param int   $target_height 画布高度
     * @param int   $new_width     图像缩放后宽度
     * @param int   $new_height    图像缩放后高度
     *
     * @return \Intervention\Image\Image
     */
    protected function doResizeExtend($image, $background, $target_width, $target_height, $new_width, $new_height, $position = null)
    {
        $x = round(($target_width - $new_width) / 2);
        $y = round(($target_height - $new_height) / 2);

        //dd($target_width, $target_height, $new_width, $new_height, $x, $y);

        $canvas = $this->imageManager->canvas($target_width, $target_height, $background);
        $targetImage = $image->resize($new_width, $new_height);

        $canvas->insert($targetImage, 'top_left', $x, $y);
        $targetImage->destroy();
        return $canvas;
    }

    /**
     * 创建GIF二进制code
     *
     * @return string
     */
    private function makeGif()
    {
        $frames = $durations = [];
        foreach ($this->gif as $key => $image) {
            $frames[$key] = $image->getCore();
            $durations[$key] = $this->frames[$key]['duration'];
        }

        $gc = new GifCreator();
        $gc->create($frames, $durations);
        $gifBinary = $gc->getGif();

        return $gifBinary;
    }
}