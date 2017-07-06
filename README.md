# PHP 图像处理类库

一款多功能的PHP图像处理类库，还支持动态gif图像

### 安装
```php
composer require "lshorz/imagelib"
```

### 使用
```php
<?php
use Lshorz\Imagelib\Image;

$file = 'uploads/test.gif'; //image path

$img = Image::open($file);

$img->thumb(300, 200, null, $img::THUMB_AUTO)->save('uploads/test_thumb.gif');

?>
```
或各种方法的链式操作

具体方法可参见 AbstractImage.php

```php
<?php
use Lshorz\Imagelib\Image;

$file = 'uploads/test.jpg';
$warter = 'assest/water.png';

$img = Image::open($file);

$imageCode = $img->resize(500, 500)->waterMark($warter, $img::POS_BOTTOM_RIGHT)->encode('data-url', 90);

echo "<img src='{$imageCode}' />";

?>

```

引用列表
- intervention/image
- Sybio/GifFrameExtractor
- Sybio/GifCreator