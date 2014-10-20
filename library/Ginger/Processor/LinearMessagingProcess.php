<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 04.10.14 - 20:14
 */

namespace Ginger\Processor;

use Ginger\Message\MessageNameUtils;
use Ginger\Message\WorkflowMessage;
use Ginger\Message\LogMessage;
use Ginger\Processor\Task\CollectData;
use Ginger\Processor\Task\Event\TaskEntryMarkedAsRunning;
use Ginger\Processor\Task\RunChildProcess;

/**
 * Class LinearMessagingProcess
 *
 * @package Ginger\Processor
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class LinearMessagingProcess extends AbstractMessagingProcess
{
    /**
     * Start or continue the process with the help of given WorkflowEngine and optionally with given WorkflowMessage
     *
     * @param WorkflowEngine $workflowEngine
     * @param WorkflowMessage $workflowMessage
     * @return void
     */
    public function perform(WorkflowEngine $workflowEngine, WorkflowMessage $workflowMessage = null)
    {
        if (is_null($workflowMessage)) {
            $this->startWithoutMessage($workflowEngine);
            return;
        }

        $taskListEntry = $this->taskList->getNextNotStartedTaskListEntry();

        if ($taskListEntry) {
            $this->recordThat(TaskEntryMarkedAsRunning::at($taskListEntry->taskListPosition()));

            if (! MessageNameUtils::isGingerEvent($workflowMessage->getMessageName())) {
                $this->receiveMessage(
                    LogMessage::logWrongMessageReceivedFor(
                        $taskListEntry->task(),
                        $taskListEntry->taskListPosition(),
                        $workflowMessage
                    )
                );

                if (! $this->config->booleanValue(Definition::CONFIG_STOP_ON_ERROR)) {
                    $this->perform($workflowEngine);
                    return;
                }
            }

            $this->performTask($taskListEntry->task(), $taskListEntry->taskListPosition(), $workflowEngine, $workflowMessage);
            return;
        }
    }

    /**
     * @param WorkflowEngine $workflowEngine
     */
    protected function startWithoutMessage(WorkflowEngine $workflowEngine)
    {
        $taskListEntry = $this->taskList->getNextNotStartedTaskListEntry();

        if ($taskListEntry) {
            $this->recordThat(TaskEntryMarkedAsRunning::at($taskListEntry->taskListPosition()));

            $task = $taskListEntry->task();

            if (! $task instanceof CollectData && ! $task instanceof RunChildProcess) {
                $this->receiveMessage(LogMessage::logNoMessageReceivedFor($task, $taskListEntry->taskListPosition()));

                if (! $this->config->booleanValue('stop_on_error')) {
                    $this->perform($workflowEngine);
                    return;
                }
            }

            $this->performTask($task, $taskListEntry->taskListPosition(), $workflowEngine);
        }
    }
}
 