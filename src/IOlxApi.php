<?php
namespace Parhomenko\Olx;

interface IOlxApi
{
    public function user();
    public function categories();
    public function adverts();
    public function regions();
    public function cities();
    public function currencies();
}