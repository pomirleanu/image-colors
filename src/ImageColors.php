<?php

namespace Pomirleanu\ImageColors;

use Illuminate\Support\Facades\Config;

class ImageColors
{
    private $maxColors = 5;

    private $reduceBrightness = true;

    private $reduceGradient = true;

    private $delta = 255;

    private $preview_width = 150;

    private $preview_height = 150;

    private $half_delta;

    /**
     * Create a new Skeleton Instance
     */
    public function __construct()
    {
        $this->config = Config::get('image-colors');
        $this->maxColors = isset($this->config['maxColors']) ? $this->config['maxColors'] : $this->maxColors;
        $this->reduceBrightness = isset($this->config['reduceBrightness']) ? $this->config['reduceBrightness'] : $this->reduceBrightness;
        $this->reduceGradient = isset($this->config['reduceGradient']) ? $this->config['reduceGradient'] : $this->reduceGradient;
        $this->delta = isset($this->config['delta']) ? $this->config['delta'] : $this->delta;
        // constructor body
    }

    public function get($image, $maxColors = null, $reduceBrightness = null, $reduceGradient = null, $delta = null)
    {
        if (is_readable($image)) {
            $this->overrideTheConfigForImage($maxColors, $reduceBrightness, $reduceGradient, $delta);
            if ($this->delta > 2) {
                $this->half_delta = $this->delta / 2 - 1;
            } else {
                $this->half_delta = 0;
            }
            // WE HAVE TO RESIZE THE IMAGE, BECAUSE WE ONLY NEED THE MOST SIGNIFICANT COLORS.
            $dataImage = getimagesize($image);
            $image_scale = $this->scaleImage($dataImage[0], $dataImage[1]);
            $image_resized = imagecreatetruecolor($image_scale['width'], $image_scale['height']);
            if ($dataImage[2] == 1)
                $image_orig = imagecreatefromgif($image);
            if ($dataImage[2] == 2)
                $image_orig = imagecreatefromjpeg($image);
            if ($dataImage[2] == 3)
                $image_orig = imagecreatefrompng($image);
            // WE NEED NEAREST NEIGHBOR RESIZING, BECAUSE IT DOESN'T ALTER THE COLORS
            imagecopyresampled($image_resized, $image_orig, 0, 0, 0, 0, $image_scale['width'], $image_scale['height'], $dataImage[0], $dataImage[1]);
            return $this->getTheHexArray($image_resized);
        } else {
            $this->error = "Image " . $image . " does not exist or is unreadable.";
            return false;
        }
    }

    private function overrideTheConfigForImage($maxColors, $reduceBrightness, $reduceGradient, $delta)
    {
        if (isset($maxColors)) {
            $this->maxColors = $maxColors;
        }
        if (isset($reduceBrightness)) {
            $this->reduceBrightness = $reduceBrightness;
        }
        if (isset($reduceGradient)) {
            $this->reduceGradient = $reduceGradient;
        }
        if (isset($delta)) {
            $this->delta = $delta;
        }
    }

    private function scaleImage($width, $height)
    {
        $scale = 1;
        if ($width > 0)
            $scale = min($this->preview_width / $width, $this->preview_height / $height);
        if ($scale < 1) {
            return ['width' => floor($scale * $width), 'height' => floor($scale * $width)];
        } else {
            return ['width' => $width, 'height' => $height];
        }
    }

    private function getTheHexArray($image)
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $pixelCount = 0;
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $pixelCount++;
                $index = imagecolorat($image, $x, $y);
                $colors = imagecolorsforindex($image, $index);
                // ROUND THE COLORS, TO REDUCE THE NUMBER OF DUPLICATE COLORS
                if ($this->delta > 1) {
                    $colors = $this->roundTheColors($colors);
                }
                $hex = substr("0" . dechex($colors['red']), -2) . substr("0" . dechex($colors['green']), -2) . substr("0" . dechex($colors['blue']), -2);
                if (!isset($hexArray[$hex])) {
                    $hexArray[$hex] = 1;
                } else {
                    $hexArray[$hex]++;
                }
            }
        }

        if ($this->reduceBrightness) {
            $hexArray = $this->reduceGradients($hexArray);
        }

        if ($this->reduceBrightness) {
            $hexArray = $this->reduceBrightness($hexArray);
        }
        arsort($hexArray, SORT_NUMERIC);

        foreach ($hexArray as $key => $value) {
            $hexArray[$key] = (float)(($value / $pixelCount) * 100);
        }

        if ($this->maxColors > 0) {
            $hexArray = array_slice($hexArray, 0, $this->maxColors, true);
        }
        return $hexArray;
    }

    private function roundTheColors($colors)
    {
        $colors['red'] = intval((($colors['red']) + $this->half_delta) / $this->delta) * $this->delta;
        $colors['green'] = intval((($colors['green']) + $this->half_delta) / $this->delta) * $this->delta;
        $colors['blue'] = intval((($colors['blue']) + $this->half_delta) / $this->delta) * $this->delta;
        if ($colors['red'] >= 256) {
            $colors['red'] = 255;
        }
        if ($colors['green'] >= 256) {
            $colors['green'] = 255;
        }
        if ($colors['blue'] >= 256) {
            $colors['blue'] = 255;
        }

        return $colors;
    }

    private function reduceBrightness($hexArray)
    {
        arsort($hexArray, SORT_NUMERIC);

        $brightness = [];
        foreach ($hexArray as $hex => $num) {
            if (!isset($brightness[$hex])) {
                $new_hex = $this->normalize($hex, $hexArray);
                $brightness[$hex] = $new_hex;
            } else {
                $new_hex = $brightness[$hex];
            }

            if ($hex != $new_hex) {
                $hexArray[$hex] = 0;
                $hexArray[$new_hex] += $num;
            }
        }
        return $hexArray;
    }

    private function normalize($hex, $hexArray)
    {
        $lowest = 255;
        $highest = 0;
        $colors['red'] = hexdec(substr($hex, 0, 2));
        $colors['green'] = hexdec(substr($hex, 2, 2));
        $colors['blue'] = hexdec(substr($hex, 4, 2));

        if ($colors['red'] < $lowest) {
            $lowest = $colors['red'];
        }
        if ($colors['green'] < $lowest) {
            $lowest = $colors['green'];
        }
        if ($colors['blue'] < $lowest) {
            $lowest = $colors['blue'];
        }

        if ($colors['red'] > $highest) {
            $highest = $colors['red'];
        }
        if ($colors['green'] > $highest) {
            $highest = $colors['green'];
        }
        if ($colors['blue'] > $highest) {
            $highest = $colors['blue'];
        }

        // Do not normalize white, black, or shades of grey unless low delta
        if ($lowest == $highest) {
            if ($this->delta <= 32) {
                if ($lowest == 0 || $highest >= (255 - $this->delta)) {
                    return $hex;
                }
            } else {
                return $hex;
            }
        }

        for (; $highest < 256; $lowest += $this->delta, $highest += $this->delta) {
            $new_hex = substr("0" . dechex($colors['red'] - $lowest), -2) . substr("0" . dechex($colors['green'] - $lowest), -2) . substr("0" . dechex($colors['blue'] - $lowest), -2);

            if (isset($hexArray[$new_hex])) {
                // same color, different brightness - use it instead
                return $new_hex;
            }
        }

        return $hex;
    }

    private function reduceGradients($hexArray)
    {
        arsort($hexArray, SORT_NUMERIC);

        $gradients = array();
        foreach ($hexArray as $hex => $num) {
            if (!isset($gradients[$hex])) {
                $new_hex = $this->findAdjacent($hex, $gradients);
                $gradients[$hex] = $new_hex;
            } else {
                $new_hex = $gradients[$hex];
            }

            if ($hex != $new_hex) {
                $hexArray[$hex] = 0;
                $hexArray[$new_hex] += $num;
            }
        }
        return $hexArray;
    }

    private function findAdjacent($hex, $gradients)
    {
        $red = hexdec(substr($hex, 0, 2));
        $green = hexdec(substr($hex, 2, 2));
        $blue = hexdec(substr($hex, 4, 2));

        if ($red > $this->delta) {
            $new_hex = substr("0" . dechex($red - $this->delta), -2) . substr("0" . dechex($green), -2) . substr("0" . dechex($blue), -2);
            if (isset($gradients[$new_hex])) {
                return $gradients[$new_hex];
            }
        }
        if ($green > $this->delta) {
            $new_hex = substr("0" . dechex($red), -2) . substr("0" . dechex($green - $this->delta), -2) . substr("0" . dechex($blue), -2);
            if (isset($gradients[$new_hex])) {
                return $gradients[$new_hex];
            }
        }
        if ($blue > $this->delta) {
            $new_hex = substr("0" . dechex($red), -2) . substr("0" . dechex($green), -2) . substr("0" . dechex($blue - $this->delta), -2);
            if (isset($gradients[$new_hex])) {
                return $gradients[$new_hex];
            }
        }

        if ($red < (255 - $this->delta)) {
            $new_hex = substr("0" . dechex($red + $this->delta), -2) . substr("0" . dechex($green), -2) . substr("0" . dechex($blue), -2);
            if (isset($gradients[$new_hex])) {
                return $gradients[$new_hex];
            }
        }
        if ($green < (255 - $this->delta)) {
            $new_hex = substr("0" . dechex($red), -2) . substr("0" . dechex($green + $this->delta), -2) . substr("0" . dechex($blue), -2);
            if (isset($gradients[$new_hex])) {
                return $gradients[$new_hex];
            }
        }
        if ($blue < (255 - $this->delta)) {
            $new_hex = substr("0" . dechex($red), -2) . substr("0" . dechex($green), -2) . substr("0" . dechex($blue + $this->delta), -2);
            if (isset($gradients[$new_hex])) {
                return $gradients[$new_hex];
            }
        }

        return $hex;
    }
}
