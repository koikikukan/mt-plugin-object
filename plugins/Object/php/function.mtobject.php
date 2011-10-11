<?php
function smarty_function_mtobject($args, &$ctx) {
    $object = $ctx->stash($args['name']);
    $prop = $args['property'];
    return $object->$prop;
}
?>
