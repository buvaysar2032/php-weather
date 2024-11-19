<?php

/*
 * CKFinder
 * ========
 * https://ckeditor.com/ckfinder/
 * Copyright (c) 2007-2023, CKSource Holding sp. z o.o. All rights reserved.
 *
 * The software, this file and its contents are subject to the CKFinder
 * License. Please read the license.txt file before using, installing, copying,
 * modifying or distribute this file or part of its contents. The contents of
 * this file is part of the Source Code of CKFinder.
 */

namespace CKSource\CKFinder\Event;

use CKSource\CKFinder\{CKFinder, ResizedImage\ResizedImageAbstract};
use JetBrains\PhpStorm\Pure;

/**
 * The ResizeImageEvent class.
 */
class ResizeImageEvent extends CKFinderEvent
{
    /**
     * @param CKFinder $app The CKFinder instance.
     */
    #[Pure]
    public function __construct(CKFinder $app, protected ResizedImageAbstract $resizedImage)
    {
        parent::__construct($app);
    }

    /**
     * Returns the resized image object.
     */
    public function getResizedImage(): ResizedImageAbstract
    {
        return $this->resizedImage;
    }

    /**
     * Sets the resized image object.
     */
    public function setResizedImage(ResizedImageAbstract $resizedImage): void
    {
        $this->resizedImage = $resizedImage;
    }
}
