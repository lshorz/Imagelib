<?php
namespace Lshorz\Imagelib;

use Intervention\Image\ImageManager;

abstract class AbstractImage
{
    /* 缩略图相关常量定义 */
    const THUMB_AUTO = 1; //缩略图等比例自动缩放
    const THUMB_FIT = 2;  //缩略图自动缩放并裁剪(可选择裁剪位置)
    const THUMB_FILL_RESIZE = 3; //缩略图保持原图比例缩放后填充
    const THUMB_FILL_SCALE_RESIZE = 4; //缩略图按比例占满填充
    const THUMB_FORCE_RESIZE = 5; //缩略图固定缩放占满填充

    /**
     * 位置定义
     * -----------------
     * 1      2      3
     * 4      5      6
     * 7      8      9
     * -----------------
     */
    const POS_TOP_LEFT = 1;
    const POS_TOP_CENTER = 2;
    const POS_TOP_RIGHT = 3;
    const POS_LEFT_CENTER = 4;
    const POS_CENTER_CENTER = 5;
    const POS_RIGHT_CENTER = 6;
    const POS_BOTTOM_LEFT = 7;
    const POS_BOTTOM_CENTER = 8;
    const POS_BOTTOM_RIGHT = 9;

    /* 翻转相关常量定义 */
    const FLIP_H = 'h'; //X轴翻转(水平)
    const FLIP_V = 'v'; //Y轴翻转(垂直)

    /**
     * @var ImageManager
     */
    protected $imageManager;

    /**
     * @var \Intervention\Image\Image
     */
    protected $image;

    /**
     * @var array
     */
    protected $frames = [];


    protected $gif;

    /**
     * @var array
     */
    protected $info;

    public function __construct(array $driver = null)
    {
        $this->imageManager = new ImageManager();
        if (is_array($driver)) {
            $this->imageManager->configure($driver);
        }
    }

    /**
     * 准备要处理的图像
     *
     * @return $this
     */
    abstract public function open($file);

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
    abstract public function thumb($width, $height, $background, $type, $position);

    /**
     * 创建水印
     *
     * @param mixed $waterImg 水印文件
     * @param int   $position 水印位置
     * @param int   $alpha    水印图片透明度
     *
     * @return $this
     */
    abstract  public function waterMark($waterImg, $position, $alpha);

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
    abstract public function text($text, $size, $color, $position, $angle, $fontType);


    /**
     * 获取图像信息
     *
     * @return array
     */
    abstract public function getInfo();

    /**
    * 保存图像
    *
    * @param string $path
    * @param int    $quality 图像质量
    *
    * @return boolean
    */
    abstract  public function save($path, $quality);

    /**
     * 生成图像http响应
     *
     * @return mixed
     */
    abstract public function response($format, $quality);

    /**
     * 以特定图像格式生成相关编码
     *
     * @param string $format  图像格式[jpg|png|gif|tip|bmp|data-url]
     * @param int    $quality 图像质量
     *
     * @return mixed
     */
    abstract public function encode($format, $quality);

    /**
     * 图像大小
     *
     * @param int  $width
     * @param int  $height
     * @param bool $ratio 自动计算比例
     *
     * @return $this
     */
    abstract public function resize($width, $height, $ratio);

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
    abstract public function crop($width, $height, $x, $y);

    /**
     * 图像旋转
     *
     * @param float $angle       (-90 顺时针, 90逆时针)
     * @param mixed $background  背景色
     *                           [null:透明需png支持, '#00ff00', 'rgba(0, 0, 0, 0.5)', array(255,255,255, 0.5)]
     *
     * @return $this
     */
    abstract public function rotate($angle, $background);

    /**
     * 翻转图像
     *
     * @param int $model
     *
     * @return $this
     */
    abstract public function flip($model);

    /**
     * 将图像转成灰度
     *
     * @return $this
     */
    abstract public function greyscale();

    /**
     * 将图像像素化
     *
     * @param int $size
     */
    abstract public function pixelate($size);


    /**
     * 根据常量重新生成相关定位
     *
     * @param int $pos 定位参数
     *
     * @return array
     */
    protected function getPosition($pos = self::POS_CENTER_CENTER)
    {
        $position = [];
        switch ($pos) {
            case self::POS_TOP_LEFT:
                $position['x'] = 'left';
                $position['y'] = 'top';
                $position['pos'] = 'top-left';
                break;
            case self::POS_TOP_CENTER:
                $position['x'] = 'center';
                $position['y'] = 'top';
                $position['pos'] = 'top';
                break;
            case self::POS_TOP_RIGHT:
                $position['x'] = 'right';
                $position['y'] = 'top';
                $position['pos'] = 'top-right';
                break;
            case self::POS_LEFT_CENTER:
                $position['x'] = 'left';
                $position['y'] = 'center';
                $position['pos'] = 'left';
                break;
            case self::POS_RIGHT_CENTER:
                $position['x'] = 'right';
                $position['y'] = 'center';
                $position['pos'] = 'right';
                break;
            case self::POS_BOTTOM_LEFT:
                $position['x'] = 'left';
                $position['y'] = 'bottom';
                $position['pos'] = 'bottom-left';
                break;
            case self::POS_BOTTOM_CENTER:
                $position['x'] = 'center';
                $position['y'] = 'bottom';
                $position['pos'] = 'bottom';
                break;
            case self::POS_BOTTOM_RIGHT:
                $position['x'] = 'right';
                $position['y'] = 'bottom';
                $position['pos'] = 'bottom-right';
                break;
            default:
                $position['x'] = 'center';
                $position['y'] = 'center';
                $position['pos'] = 'center';
                break;
        }
        return $position;
    }

}