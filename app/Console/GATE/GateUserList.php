<?php

namespace App\Console\GATE;


use App\Models\GateList;
use App\Models\GateUser;
use GateApi\Api\SpotApi;
use GateApi\Configuration;
use Illuminate\Console\Command;
use GuzzleHttp\Client;

class GateUserList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'GateUser';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '获取gate用户USDT可用余额';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $user = GateUser::query()->get();

        try {
            foreach ($user as $value) {

                $config = Configuration::getDefaultConfiguration()
                    ->setKey($value->key)
                    ->setSecret($value->secret);


                $apiInstance = new SpotApi(
                    new Client(),
                    $config
                );

                $associate_array['currency'] = 'USDT';

                $result = $apiInstance->listSpotAccounts($associate_array);
                $value->available=json_decode($result[0])->available;
                $value->save();
            }

        } catch (\Exception $e) {
            echo 'Exception when calling0 SpotApi->listCurrencies: ', $e->getMessage(), PHP_EOL;
        }
    }
}
