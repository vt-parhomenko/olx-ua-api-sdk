<?php
namespace Parhomenko\Olx;

interface IOlxApi
{
    public function user();
    public function categories();
    public function adverts();
    public function regions();
    public function cities();
    public function districts();
    public function currencies();
    public function users();
}