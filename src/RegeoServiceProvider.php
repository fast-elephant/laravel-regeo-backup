<?php


namespace FastElephant\LaravelRegeo;


use Illuminate\Support\ServiceProvider;

class RegeoServiceProvider extends ServiceProvider
{
    /**
     * 启动应用服务
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            $this->getConfigFile() => config_path('regeo.php'),
        ]);
    }

    /**
     * 在容器中注册绑定。
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            $this->getConfigFile(), 'regeo'
        );
    }

    protected function getConfigFile()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'regeo.php';
    }
}
