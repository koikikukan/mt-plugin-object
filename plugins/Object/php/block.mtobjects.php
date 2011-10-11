<?php
function smarty_block_mtobjects($args, $content, &$ctx, &$repeat) {
    $name = $args['name'];
    $localvars = array($name, $name.'_counter', 'blog_id', 'blog', 'conditional');
    if (!isset($content)) {
        $ctx->localize($localvars);
        if (isset($args['start'])) {
            $args['current_timestamp'] =  $args['start'];
        }
        if (isset($args['end'])) {
            $args['current_timestamp_end'] = $args['end'];
        }
        if (isset($args['id'])) {
            $args['entry_id'] = $args['id'];
        }
        $blog = $ctx->stash('blog');
        if ($blog)
            $args['blog_id'] = $blog->blog_id;

        if ($name == 'asset') {
            $objects = $ctx->mt->db()->fetch_assets($args);
        } else if ($name == 'author') {
            $objects = $ctx->mt->db()->fetch_authors($args);
        } else if ($name == 'blog') {
            $objects = $ctx->mt->db()->fetch_blogs($args);
        } else if ($name == 'category') {
            $objects = $ctx->mt->db()->fetch_categories($args);
        } else if ($name == 'comment') {
            $objects = $ctx->mt->db()->fetch_comments($args);
        } else if ($name == 'entry') {
            $objects = $ctx->mt->db()->fetch_entries($args);
        } else if ($name == 'folder') {
            $objects = $ctx->mt->db()->fetch_folders($args);
        } else if ($name == 'ping') {
            $objects = $ctx->mt->db()->fetch_pings($args);
        }
        $ctx->stash($name . '_obj', $objects);
        $counter = 0;
    } else {
        $objects = $ctx->stash($name . '_obj');
        $counter = $ctx->stash($name . '_counter');
    }

    if (empty($objects)) {
        $ret = $ctx->_hdlr_if($args, $content, $ctx, $repeat, 0);
        if (!$repeat)
              $ctx->restore($localvars);
        return $ret;
    }

    $ctx->stash('conditional', empty($objects) ? 0 : 1);
    if ($counter < count($objects)) {
        $blog_id = $ctx->stash('blog_id');
        $object = $objects[$counter];
        if (!empty($object)) {
            $ctx->stash($name, $object);
            $ctx->stash($name . '_counter', $counter + 1);
            $repeat = true;
            $count = $counter + 1;
            $ctx->__stash['vars']['__counter__'] = $count;
            $ctx->__stash['vars']['__odd__'] = ($count % 2) == 1;
            $ctx->__stash['vars']['__even__'] = ($count % 2) == 0;
            $ctx->__stash['vars']['__first__'] = $count == 1;
            $ctx->__stash['vars']['__last__'] = ($count == count($objects));
        }
    } else {
        $ctx->restore($localvars);
        $repeat = false;
    }
    return $content;
}
?>
