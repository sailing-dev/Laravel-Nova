<?php

namespace App\Providers;
 
use App\Nova\Dashboards\FbReporting\WebsiteBreakDownDashboard;
use App\Nova\Dashboards\FbReporting\CampaignDetailsDashboard;
use App\Nova\Dashboards\FbReporting\CreateCampaignsFromRelatedDashboard;
use App\Nova\Dashboards\FbReporting\CreateCampaignsFromTemplateDashboard;
use App\Nova\Dashboards\FbReporting\FbPagePostsDashboard;
use App\Nova\Dashboards\FbReporting\IgAccountLoaderDashboard;
use App\Nova\Dashboards\FbReporting\SubmitKeywordsDashboard;
use FbReporting\TypeDailyPerfCard\TypeDailyPerfCard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Register the Nova routes.
     *
     * @return void
     */
    protected function routes()
    {
        Nova::routes()
                ->withAuthenticationRoutes()
                ->withPasswordResetRoutes()
                ->register();
    }

    /**
     * Register the Nova gate.
     *
     * This gate determines who can access Nova in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewNova', function ($user) {
            if (in_array($user->email, ['unit-tester@revenuedriver.com'])) {
                return true;
            }
            else if ($user != null) {
                return true;
            }
            Auth::logout();
            return false;
        });
        Gate::authorize('viewNova');
    }

    /**
     * Get the cards that should be displayed on the default Nova dashboard.
     *
     * @return array
     */
    protected function cards()
    {
        if (auth()->user()->email != 'fbreview@revenuedriver.com') {
            return [
                (new TypeDailyPerfCard())->dailyTotalsByTag(), 
            ];
        }
        else {
            return [];
        }
    }

    /**
     * Get the extra dashboards that should be displayed on the Nova dashboard.
     *
     * @return array
     */
    protected function dashboards()
    {
        if (auth()->user()->email != 'fbreview@revenuedriver.com') {
            return [
                new WebsiteBreakDownDashboard(),
                new CampaignDetailsDashboard(),
                new SubmitKeywordsDashboard(),
                new CreateCampaignsFromRelatedDashboard(),
                new IgAccountLoaderDashboard(),
                new FbPagePostsDashboard(),
                // new CreateCampaignsFromTemplateDashboard()
            ];
        }
        return [];
    }

    /**
     * Get the tools that should be listed in the Nova sidebar.
     *
     * @return array
     */
    public function tools()
    {
        return [];
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
