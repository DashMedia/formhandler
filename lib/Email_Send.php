<?php

interface Email_Send
{
    public function send($to, $from);
    public function setFields($fields);
    public function setHtmlContent($content);
    public function setPlainContent($content);
}