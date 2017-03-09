<?php

namespace Foolz\FoolFuuka\Controller\Api;

use Foolz\FoolFuuka\Plugins\ThreadChunk\Model\ThreadChunk as TC;
use Foolz\FoolFuuka\Model\CommentBulk;

class ThreadChunk extends \Foolz\FoolFuuka\Controller\Api\Chan
{
    /**
     * @var TC
     */
    protected $thread_chunk;

    public function before()
    {
        $this->thread_chunk = $this->getContext()->getService('foolfuuka-plugin.chunk');
        parent::before();
    }

    public function get_chunk()
    {
        $response = [];
        if (!$this->check_board()) {
            return $this->response->setData(['error' => _i('No board selected.')])->setStatusCode(422);
        }
        $num = $this->getQuery('num');
        $posts = $this->getQuery('posts');
        $start = $this->getQuery('start');
        if(!is_numeric($num))
            return $this->response->setData(['error' => _i('Invalid thread number.')]);
        if(!is_numeric($posts))
            return $this->response->setData(['error' => _i('Invalid first control number.')]);
        if(!is_numeric($start))
            return $this->response->setData(['error' => _i('Invalid second control number.')]);

        $thread = $this->thread_chunk->ThreadStatus($this->radix, $num);
        if (!$thread) {
            return $this->response->setData(['error' => _i('There\'s no such a thread.')]);
        }

        if ($start !== 1) {
            $r_start = ($start - 1) * $posts;
        } else {
            $r_start = 0;
        }

        if ($r_start == 1) {
            $omit = $thread['nreplies'] - $posts;
        } else {
            $omit = $thread['nreplies'] - (($start - 1) * $posts) - $posts;
        }
        if ($omit <= 0) {
            $omit = 0;
        }

        $response['chunk']['posts'] = $posts;
        $response['chunk']['page'] = $start;
        $response['chunk']['start'] = ($start - 1) * $posts;
        $response['chunk']['omitted'] = $omit;

        try {
            $response['comments'] = $this->thread_chunk->getThreadChunk($this->radix, $num, (int)$posts, (int)$r_start);
        } catch (\Exception $e) {
            return $this->response->setData(['error' => $e->getMessage()]);
        }

        return $this->response->setData($response);
    }
}