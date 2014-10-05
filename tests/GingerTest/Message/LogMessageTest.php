<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 03.10.14 - 20:02
 */

namespace GingerTest\Message;

use Ginger\Message\LogMessage;
use Ginger\Processor\ProcessId;
use Ginger\Processor\Task\CollectData;
use Ginger\Processor\Task\TaskListId;
use Ginger\Processor\Task\TaskListPosition;
use GingerTest\Mock\UserDictionary;
use GingerTest\TestCase;

/**
 * Class LogMessageTest
 *
 * @package GingerTest\Message
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class LogMessageTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_log_a_warning_msg()
    {
        $taskListPosition = TaskListPosition::at(TaskListId::linkWith(ProcessId::generate()), 1);

        $message = LogMessage::logWarningMsg("A simple warning msg", $taskListPosition);

        $this->assertEquals('A simple warning msg', $message->getTechnicalMsg());
        $this->assertTrue($message->isWarning());
        $this->assertTrue($taskListPosition->equals($message->getProcessTaskListPosition()));
    }

    /**
     * @test
     */
    public function it_can_log_a_debug_msg()
    {
        $taskListPosition = TaskListPosition::at(TaskListId::linkWith(ProcessId::generate()), 1);

        $message = LogMessage::logDebugMsg("A simple debug msg", $taskListPosition);

        $this->assertEquals('A simple debug msg', $message->getTechnicalMsg());
        $this->assertTrue($message->isDebug());
        $this->assertTrue($taskListPosition->equals($message->getProcessTaskListPosition()));
    }

    /**
     * @test
     */
    public function it_can_log_a_data_processing_started_on_msg()
    {
        $taskListPosition = TaskListPosition::at(TaskListId::linkWith(ProcessId::generate()), 1);

        $message = LogMessage::logInfoDataProcessingStarted($taskListPosition);

        $this->assertEquals('Data processing was started', $message->getTechnicalMsg());
        $this->assertTrue($message->isInfo());
        $this->assertTrue($taskListPosition->equals($message->getProcessTaskListPosition()));
    }

    /**
     * @test
     */
    public function it_can_log_an_exception_and_set_msg_code_to_500_if_no_code_is_specified()
    {
        $taskListPosition = TaskListPosition::at(TaskListId::linkWith(ProcessId::generate()), 1);

        $exception = new \RuntimeException("Internal error");

        $message = LogMessage::logException($exception, $taskListPosition);

        $this->assertEquals('Internal error', $message->getTechnicalMsg());
        $this->assertTrue($message->isError());
        $this->assertEquals(500, $message->getMsgCode());
        $this->assertTrue($taskListPosition->equals($message->getProcessTaskListPosition()));
        $this->assertTrue(isset($message->getMsgParams()['trace']));
    }

    /**
     * @test
     */
    public function it_logs_exception_and_uses_exception_code_for_msg_code_if_specified()
    {
        $taskListPosition = TaskListPosition::at(TaskListId::linkWith(ProcessId::generate()), 1);

        $exception = new \DomainException("Data cannot be found", 404);

        $message = LogMessage::logException($exception, $taskListPosition);

        $this->assertEquals('Data cannot be found', $message->getTechnicalMsg());
        $this->assertTrue($message->isError());
        $this->assertEquals(404, $message->getMsgCode());
        $this->assertTrue($taskListPosition->equals($message->getProcessTaskListPosition()));
        $this->assertTrue(isset($message->getMsgParams()['trace']));
    }

    /**
     * @test
     */
    public function it_only_accepts_error_code_greater_than_399_otherwise_it_uses_500_as_code()
    {
        $taskListPosition = TaskListPosition::at(TaskListId::linkWith(ProcessId::generate()), 1);

        $exception = new \DomainException("Data cannot be found", 399);

        $message = LogMessage::logException($exception, $taskListPosition);

        $this->assertEquals('Data cannot be found', $message->getTechnicalMsg());
        $this->assertTrue($message->isError());
        $this->assertEquals(500, $message->getMsgCode());
        $this->assertTrue($taskListPosition->equals($message->getProcessTaskListPosition()));
        $this->assertTrue(isset($message->getMsgParams()['trace']));
    }

    /**
     * @test
     */
    public function it_logs_no_message_received_for_task_as_error()
    {
        $taskListPosition = TaskListPosition::at(TaskListId::linkWith(ProcessId::generate()), 1);

        $task = CollectData::from('crm', UserDictionary::prototype());

        $message = LogMessage::logNoMessageReceivedFor($task, $taskListPosition);

        $this->assertTrue($message->isError());
        $this->assertEquals(412, $message->getMsgCode());
        $this->assertTrue($taskListPosition->equals($message->getProcessTaskListPosition()));
        $this->assertTrue(isset($message->getMsgParams()['task_class']));
        $this->assertTrue(isset($message->getMsgParams()['task_as_json']));

        $this->assertEquals(get_class($task), $message->getMsgParams()['task_class']);
        $this->assertEquals(json_encode($task->getArrayCopy()), $message->getMsgParams()['task_as_json']);
    }
}
 