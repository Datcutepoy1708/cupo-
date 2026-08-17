<?php

use App\Services\SettingService;

if (! function_exists('setting')) {
    /**
     * Helper truy xuat setting toan he thong
     *
     * @param  string|array|null  $key
     * @param  mixed  $default
     * @return mixed|SettingService
     */
    function setting($key = null, $default = null)
    {
        $service = app(SettingService::class);

        if (is_null($key)) {
            return $service;
        }

        if (is_array($key)) {
            $service->updateMany($key);

            return null;
        }

        return $service->get($key, $default);
    }
}
