<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        $db = db_connect();
        return view('home', [
            'navigationGroups'  => config('SiteNavigation')->groups,
            'currentUser'       => session()->get('authUser'),
            'registerNotice'    => session()->getFlashdata('registerNotice'),
            'registerErrors'    => session()->getFlashdata('registerErrors') ?? [],
            'registerFailed'    => session()->getFlashdata('registerFailed') ?? false,
            'registrationData'  => session()->getFlashdata('registrationData') ?? [],
            'loginNotice'       => session()->getFlashdata('loginNotice'),
            'actionNotice'      => session()->getFlashdata('actionNotice'),
            'actionNoticeType'  => session()->getFlashdata('actionNoticeType') ?? 'success',
            'contactNotice'     => session()->getFlashdata('contactNotice'),
            'contactErrors'     => session()->getFlashdata('contactErrors') ?? [],
            'contactFailed'     => session()->getFlashdata('contactFailed') ?? false,
            'contactData'       => session()->getFlashdata('contactData') ?? [],
            'openPanel'         => session()->getFlashdata('openPanel') ?? '',
            'publicServices'    => $db->table('service_categories')->where('is_available', 1)->orderBy('id')->get()->getResultArray(),
            'publicProducts'    => $db->table('products')->where('is_available', 1)->orderBy('category')->orderBy('name')->get()->getResultArray(),
        ]);
    }
}
