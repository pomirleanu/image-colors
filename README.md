# pomirleanu/image-colors
[![Software License][ico-license]](LICENSE.md)


Simple way to get the colors from a given image, will give tou the percentage and the colors hex.

## Install

Via Composer

``` bash
$ composer require pomirleanu/image-colors
```

## Next, you should add the ImageColorsServiceProvider to the providers array of your config/app.php configuration file:

``` php
Pomirleanu\ImageColors\ImageColorsServiceProvider::class,
```
## Usage | This is just a simple example
``` php
namespace ****;
use Illuminate\Http\Request;
use Pomirleanu\ImageColors;

class ImageClass
{
    /**
     * @var ImageColors
     */
    private $imageColors;

    /**
     * ImagesController constructor.
     * @param ImageColors $imageColors
     */
    public function __construct(ImageColors $imageColors)
    {
        $this->imageColors = $imageColors;
    }
    
    public function getColors(Request $request){
        if ($request->hasFile('image')) {
                $colors = $this->imageColors->get($request->image);
                
                //Do what you want with the colors
        }     
    }
}
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email pomirleanu.florentin@gmail.com instead of using the issue tracker.

## Credits

- [Pomirleanu Florentin Cristinel][https://github.com/pomirleanu]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/:vendor/:package_name.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/:vendor/:package_name/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/:vendor/:package_name.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/:vendor/:package_name.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/:vendor/:package_name.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/:vendor/:package_name
[link-travis]: https://travis-ci.org/:vendor/:package_name
[link-scrutinizer]: https://scrutinizer-ci.com/g/:vendor/:package_name/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/:vendor/:package_name
[link-downloads]: https://packagist.org/packages/:vendor/:package_name
[link-author]: https://github.com/:author_username
[link-contributors]: ../../contributors
