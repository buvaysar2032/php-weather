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

use CKSource\CKFinder\{CKFinder, Command\CommandAbstract};
use JetBrains\PhpStorm\Pure;

/**
 * The BeforeCommandEvent event class.
 */
class BeforeCommandEvent extends CKFinderEvent
{
    /**
     * Constructor.
     * @param string          $commandName   The command name.
     * @param CommandAbstract $commandObject The object of the command to be executed.
     */
    #[Pure]
    public function __construct(CKFinder $app, protected string $commandName, protected CommandAbstract $commandObject)
    {
        parent::__construct($app);
    }

    /**
     * Returns the command object.
     */
    public function getCommandObject(): CommandAbstract
    {
        return $this->commandObject;
    }

    /**
     * Sets the object of the command to be executed.
     */
    public function setCommandObject(CommandAbstract $commandObject): void
    {
        $this->commandObject = $commandObject;
    }

    /**
     * Returns the name of the command.
     */
    public function getCommandName(): string
    {
        return $this->commandName;
    }
}
