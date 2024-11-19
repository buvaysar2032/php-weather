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

use CKSource\CKFinder\CKFinder;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\HttpFoundation\Response;

/**
 * The AfterCommandEvent event class.
 */
class AfterCommandEvent extends CKFinderEvent
{
    /**
     * Constructor.
     *
     * @param CKFinder $app         The CKFinder instance.
     * @param string   $commandName The command name.
     * @param Response $response    The response object received from the command.
     */
    #[Pure]
    public function __construct(CKFinder $app, protected string $commandName, protected Response $response)
    {
        parent::__construct($app);
    }

    /**
     * Returns the response object received from the command.
     */
    public function getResponse(): Response
    {
        return $this->response;
    }

    /**
     * Sets the response to be returned.
     */
    public function setResponse(Response $response): void
    {
        $this->response = $response;
    }
}
