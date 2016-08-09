<?php

namespace Foolz\FoolFuuka\Controller\Chan;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Foolz\Plugin\Event;
use Foolz\FoolFuuka\Plugins\ThreadChunk\Model\ThreadChunk as chunk;
use Foolz\FoolFrame\Model\Context;
use Foolz\Plugin\Plugin;

class ThreadChunk extends \Foolz\FoolFuuka\Controller\Chan
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var chunk
     */
    protected $chunk;

    /**
     * @var Plugin
     */
    protected $plugin;

    public function before()
    {
        $this->context = $this->getContext();
        $this->chunk = $this->context->getService('foolfuuka-plugin.chunk');
        $this->plugin = $this->context->getService('plugins')->getPlugin('foolz/foolfuuka-plugin-thread-chunk');
        parent::before();
    }

    public function radix_chunk($num = 0, $posts = 500, $start = 0)
    {
        $this->response = new StreamedResponse();

        if(!is_numeric($num))
            return $this->error(_i('Invalid thread number.'));
        if(!is_numeric($posts))
            return $this->error(_i('Invalid first control number.'));
        if(!is_numeric($start))
            return $this->error(_i('Invalid second control number.'));

        $thread = $this->chunk->ThreadStatus($this->radix, $num);
        if (!$thread) {
            return $this->error(_i('There\'s no such a thread.'));
        }

        if ($start !== 0) {
            $r_start = $start * $posts;
        } else {
            $r_start = 0;
        }

        try {
            $this->builder->createPartial('body', 'board')
                ->getParamManager()
                ->setParams([
                    'board' => $this->chunk->getThreadChunk($this->radix, $num, (int)$posts, (int)$r_start)
                ]);
        } catch (\Exception $e) {
            return $this->error(_i('No more posts.'));
        }

        if ($r_start == 0) {
            $omit = $thread['nreplies'] - $posts;
        } else {
            $omit = $thread['nreplies'] - ($start * $posts) - $posts;
        }
        if ($omit <= 0) {
            $omit = 0;
        }

        $this->builder->getProps()->addTitle(_i('Thread Chunk') . ' #' . $num);
        $this->builder->getParamManager()->setParams([
            'chunk_num' => $num,
            'chunk_posts' => $posts,
            'chunk_page' => $start,
            'chunk_start' => $start * $posts,
            'chunk_omitted' => $omit,
            'chunk_js' => $this->plugin->getAssetManager()->getAssetLink('chunk.js'),
            'chunk_css' => $this->plugin->getAssetManager()->getAssetLink('style.css')
        ]);

        Event::forge(['foolfuuka.themes.default_after_body_template'])
            ->setCall('Foolz\FoolFuuka\Plugins\ThreadChunk\Model\ThreadChunk::renderunder')
            ->setPriority(5);

        Event::forge(['foolfuuka.themes.fuuka_after_body_template'])
            ->setCall('Foolz\FoolFuuka\Plugins\ThreadChunk\Model\ThreadChunk::renderunder')
            ->setPriority(5);

        $this->response->setCallback(function () {
            $this->builder->stream();
        });

        return $this->response;
    }
}