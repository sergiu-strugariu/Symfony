<?php

namespace App\Helper;

class PageBuilderHelper
{
    const FILE_TYPE = 'file';
    const TEXTAREA_TYPE = 'textarea';
    const WEIGHT = 'weight';

    const LINK = ['field' => 'links', 'type' => 'link'];
    const GALLERY = ['field' => 'galleries', 'type' => 'gallery'];
    const HIDDEN = ['field' => 'weight', 'type' => 'hidden'];
    const CHECKBOX = ['field' => 'isActive', 'type' => 'checkbox'];

    const WIDGET = 'widget';
    const SECTION = 'section';
}