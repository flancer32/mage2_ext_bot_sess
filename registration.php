<?php
/**
 * Register Magneto module on composer's 'install' event.
 *
 * User: Alex Gusev <alex@flancer64.com>
 */

\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    \Flancer32\BotSess\Config::MODULE, __DIR__);