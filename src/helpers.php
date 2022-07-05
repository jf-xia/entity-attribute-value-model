<?php

if (!function_exists('status')) {

    /**
     * Get status.
     *
     * @return array
     */
    function status()
    {
        return [0=>trans('eav::eav.disable'),1=>trans('eav::eav.enable')];
    }
}

if (!function_exists('message_toastr')) {

    /**
     * Flash a toastr message bag to session.
     *
     * @param string $message
     * @param string $type
     * @param array  $options
     *
     * @return string
     */
    function message_toastr($message = '', $type = 'success', $options = [])
    {
        $toastr = new \Illuminate\Support\MessageBag(get_defined_vars());

        \Illuminate\Support\Facades\Session::flash('toastr', $toastr);
    }
}

