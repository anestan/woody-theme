<?php

class WoodyTheme_Process extends WP_Background_Process
{
    /**
     * @var string
     */
    protected $action = 'woody_process';

    /**
     * Task
     *
     * Override this method to perform any actions required on each
     * queue item. Return the modified item for further processing
     * in the next pass through. Or, return false to remove the
     * item from the queue.
     *
     * @param mixed $item Queue item to iterate over
     *
     * @return mixed
     */
    protected function task($item)
    {
        // $logger = new WoodyTheme_Logger();
        // $logger->log($item['function']);

        if (!empty($item['function'])) {
            if (!empty($item['args'])) {
                call_user_func($item['function'], $item['args']);
            } else {
                call_user_func($item['function']);
            }
        }

        return false;
    }

    /**
     * Complete
     *
     * Override if applicable, but ensure that the below actions are
     * performed, or, call parent::complete().
     */
    protected function complete()
    {
        parent::complete();
        // Show notice to user or perform some other arbitrary task...
    }
}
