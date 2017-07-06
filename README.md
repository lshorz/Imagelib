# PHP 图像处理类库

一款多功能的PHP图像处理类库，还支持动态gif图像

### 安装
```php
composer require "lshorz/imagelib"
```

### 使用例子
```php
<?php
use Lshorz\Imagelib\Image;

$file = 'uploads/test.gif'; //image path

$img = Image::open($file);

//生成缩略图后保存
$img->thumb(300, 200, null, $img::THUMB_AUTO)->save('uploads/test_thumb.gif');

//缩略图也支持自动计算长，高比例最后生成http响应,例如：
echo $img->thumb(null, 200)->response('gif');
?>
```
该类库会自动识别图像是否为动态图片

更多方法可参见抽像类里面的public方法 
## AbstractImage.php

或各种方法的链式操作

```php
<?php
use Lshorz\Imagelib\Image;

$file = 'uploads/test.jpg';
$warter = 'assest/water.png';

$img = Image::open($file);

//先裁剪图像再缩放，最后生成base64编码的图像code
$imageCode = $img->crop(800, 800, 0, 0)->resize(300, 200, true)->waterMark($warter, $img::POS_BOTTOM_RIGHT)->encode('data-url', 90);

//输出该图像
echo "<img src='{$imageCode}' />";

?>

```

引用列表
- intervention/image
- Sybio/GifFrameExtractor
- Sybio/GifCreator