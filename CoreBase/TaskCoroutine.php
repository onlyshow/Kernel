<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-9-1
 * Time: 下午4:25
 */

namespace Kernel\CoreBase;

use Kernel\Coroutine\CoroutineBase;
use Kernel\Coroutine\CoroutineNull;
use Kernel\Coroutine\CoroutineTaskException;
use Kernel\Memory\Pool;

class TaskCoroutine extends CoroutineBase
{
    public $id;
    public $task_proxy_data;
    public $task_id;

    public function init($task_proxy_data, $id)
    {
        $this->task_proxy_data = $task_proxy_data;
        $this->id = $id;
        $this->getCount = getTickTime();
        $this->send(function ($serv, $task_id, $data) {
            if ($data instanceof CoroutineNull) {
                $data = null;
            }
            $this->result = $data;
        });
        return $this;
    }

    public function send($callback)
    {
        $this->task_id = getInstance()->server->worker_id . getInstance()->server->task($this->task_proxy_data, $this->id, $callback);
    }

    public function destroy()
    {
        parent::destroy();
        $this->task_id = null;
        Pool::getInstance()->push($this);
    }

    protected function onTimerOutHandle()
    {
        parent::onTimerOutHandle();
        getInstance()->stopTask($this->task_id);
    }

    public function getResult()
    {
        if ($this->result instanceof CoroutineTaskException) {
            if (!$this->noException) {
                $ex = new SwooleException($this->result->getMessage(), $this->result->getCode());
                $this->destroy();
                throw $ex;
            }
        }
        return parent::getResult();
    }
}
