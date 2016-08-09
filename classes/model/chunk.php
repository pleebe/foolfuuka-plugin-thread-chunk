<?php

namespace Foolz\FoolFuuka\Plugins\ThreadChunk\Model;

use Foolz\FoolFuuka\Model\CommentBulk;
use Foolz\FoolFuuka\Model\Board;

class ThreadChunk extends Board
{
    public static function renderunder($result)
    {
        $radix = $result->getObject()->getBuilderParamManager()->getParam('radix');
        $tnum = $result->getObject()->getBuilderParamManager()->getParam('chunk_num');
        $posts = $result->getObject()->getBuilderParamManager()->getParam('chunk_posts');
        $npage = $result->getObject()->getBuilderParamManager()->getParam('chunk_page') + 1;
        $omitted = $result->getObject()->getBuilderParamManager()->getParam('chunk_omitted');
        $start = $result->getObject()->getBuilderParamManager()->getParam('chunk_start');
        ?>
        <link href="<?= $result->getObject()->getBuilderParamManager()->getParam('chunk_css') ?>" rel="stylesheet"
              type="text/css"/>
        <div class="pagecontainer"></div>
        <div class="threadchunk">Showing
            posts <?= $start ?>
            to <?= $start + $posts ?>. Posts still in
            this thread <?= $omitted ?>
            <?php if ($omitted != 0) : ?>
                <a class="btnr chunknext"
                   href="<?= $result->getObject()->getUri()->create($radix->shortname . '/chunk/' . $tnum . '/' . $posts . '/' . $npage) ?>">Load
                    next <?= $posts ?></a>
            <?php endif; ?>
        </div>
        <script src="<?= $result->getObject()->getBuilderParamManager()->getParam('chunk_js') ?>"></script>
        <?php
    }

    protected function p_ThreadStatus($radix, $num)
    {
        return $this->dc->qb()
            ->select('*')
            ->from($radix->getTable('_threads'), 't')
            ->where('thread_num = :thread_num')
            ->setParameter(':thread_num', $num)
            ->execute()
            ->fetch();
    }

    protected function p_getThreadChunk($radix, $num, $posts, $start)
    {
        $query_result = [];
        $query_result = $this->dc->qb()
            ->select('*')
            ->from($radix->getTable(), 'r')
            ->leftJoin('r', $radix->getTable('_images'), 'mg', 'mg.media_id = r.media_id')
            ->where('thread_num = :thread_num')
            ->orderBy('num', 'ASC')
            ->addOrderBy('subnum', 'ASC')
            ->setParameter(':thread_num', $num)
            ->setMaxResults($posts)
            ->setFirstResult($start)
            ->execute()
            ->fetchAll();

        if (!count($query_result)) {
            throw new \Exception();
        }

        $comments_unsorted = [];

        foreach ($query_result as $key => $row) {
            $data = new CommentBulk();
            $data->import($row, $radix);
            unset($query_result[$key]);
            $comments_unsorted[] = $data;
        }

        unset($query_result);
        $comments[$num] = [
            'omitted' => 0,
            'images_omitted' => 0
        ];

        foreach ($comments_unsorted as $key => $bulk) {
            if ($bulk->comment->op == 0) {
                $comments[$bulk->comment->thread_num]['posts']
                [$bulk->comment->num . (($bulk->comment->subnum == 0) ? '' : '_' . $bulk->comment->subnum)] = &$comments_unsorted[$key];
            } else {
                $comments[$bulk->comment->num]['op'] = &$comments_unsorted[$key];
            }
        }
        return $comments;
    }
}