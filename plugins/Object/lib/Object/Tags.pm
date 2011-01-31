package Object::Tags;

use strict;

sub _hdlr_objects {
    my ($ctx, $args, $cond) = @_;
 
    my $name = $args->{name};
    return '' if !$name;
    my $class = MT->model($name);
    return '' if !$class;

    my $start = $args->{start};
    my $end = $args->{end};
    my $incl = $args->{incl};
    my $sort = $args->{sort};
    my $status = $args->{status};

    my $res = '';
    my $builder = $ctx->stash('builder');
    my $tokens = $ctx->stash('tokens');

    my ($term, $arg);

    $arg->{ sort } = $sort if $sort;
    $arg->{ range_incl } = { $sort => 1 } if $incl && ($start && $end);
    $arg->{ range } = { $sort => 1 } if !$incl && ($start && $end);
    $term->{ $sort } = [ $start, $end ] if $sort && ($start && $end);
    $term->{ status } = $status if $status;

    my $blog = $ctx->stash('blog');
    $term->{blog_id} = $blog->id;

    if ($class =~ /Blog|Author|Session|Tag/) {
        undef $term;
    }
    if ($class eq 'MT::Asset') {
        $term->{class} = '*';
    }

    my @objects = $class->load($term, $arg);
    my $count = 0;
    my $max = scalar @objects;
    my $vars = $ctx->{__stash}{vars} ||= {};
    for my $object (@objects) {
        $count++;
        local $ctx->{__stash}{$name} = $object;
        local $vars->{__first__} = $count == 1;
        local $vars->{__last__} = ($count == ($max));
        local $vars->{__odd__} = ($count % 2) == 1;
        local $vars->{__even__} = ($count % 2) == 0;
        local $vars->{__counter__} = $count;
        my $out = $builder->build($ctx, $tokens, { %$cond });
        return $ctx->error( $builder->errstr ) unless defined $out;
        $res .= $out;
    }
    if (!@objects) {
        return MT::Template::Context::_hdlr_pass_tokens_else(@_);
    }
    $res;
}

sub _hdlr_object_data {
    my ($ctx, $args) = @_;
    my $name = $args->{name};
    my $prop = $args->{property};
    return '' unless $prop;

    my $object = $ctx->stash($name)
        or return $ctx->error();
    $object->has_column($prop)
        or return $ctx->error(MT->translate("You have an error in your '[_2]' attribute: [_1]", $prop, 'property'));
    return $object->$prop;
}

1;
