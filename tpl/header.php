<!DOCTYPE HTML>
<html class="no-js" lang="de" prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# mynamespace: http://ogp.me/ns/fb/mynamespace#">
<head itemscope itemtype="http://schema.org/WebSite" >
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>

<main>
    <div class="container">
    <?

if ($INSTALL::has_errors()) {
    $errors = $INSTALL::get_msge();
    echo '<div class="alert alert-danger">' . implode('<br>', $errors) . '</div>';
}
else {
    $msg = $INSTALL::get_msg();
    if (count($msg) > 0) {
        echo '<div class="alert alert-success">' . implode('<br>', $msg) . '</div>';
    }
}
