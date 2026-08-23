<?php

test('root route redirects guests to the login page', function () {
    $this->get(route('home'))->assertRedirect(route('login'));
});
