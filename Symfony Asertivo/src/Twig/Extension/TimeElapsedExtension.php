<?php

namespace App\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TimeElapsedExtension extends AbstractExtension {

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return array An array of filters
     */
    public function getFilters() {
        return array(
            new TwigFilter('time_elapsed', [$this, 'timeElapsed']),
        );
    }

    public function timeElapsed($value, $full = false) {
        if (empty($value)) {
            return "-";
        }

        $now = new \DateTime();
        $ago = \DateTime::createFromFormat('U', $value);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'ani',
            'm' => 'luni',
            'w' => 'saptamani',
            'd' => 'zile',
            'h' => 'ore',
            'i' => 'minute',
            's' => 'secunde',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v;
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) {
            $string = array_slice($string, 0, 1);
        }

        return $string ? implode(', ', $string) . ' in urma' : 'chiar acum';
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName() {
        return 'time_elapsed';
    }

}
