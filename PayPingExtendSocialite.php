<?php

namespace SocialiteProviders\PayPing;

use SocialiteProviders\Manager\SocialiteWasCalled;

class PayPingExtendSocialite
{
    /**
     * Register the provider.
     *
     * @param \SocialiteProviders\Manager\SocialiteWasCalled $socialiteWasCalled
     */
    public function handle(SocialiteWasCalled $socialiteWasCalled)
    {
        $socialiteWasCalled->extendSocialite(
            'payping', __NAMESPACE__.'\Provider'
        );
    }
}
