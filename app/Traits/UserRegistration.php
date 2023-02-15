<?php

namespace App\Traits;

use App\Models\Api\Muser;
use App\Models\Api\UserVa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

trait UserRegistration
{

    public function generate_va($nohp)
    {
        // $alamat = "https://apidev.zipay.id/api/Merchant/GenerateVA";
        $alamat = "https://api.zipay.id/api/Merchant/GenerateVA";
        $merchant_token = "xcNB4sm73wYy5AAswK3zTrlqTqRxy9fX/a3zLsmKrog=";

        $data = [
            "mid" => "AYOPAY",
            "merchantToken" => $merchant_token,
            "phoneNo" => $nohp
        ];

        $response = Http::withHeaders(['Content-Type' => 'application/json',])->post($alamat, $data);

        $check = json_decode($response);

        if ($check->status == 200) {
            $result_va = $check->result;
            // $result_va = $result->vacctInfoList;
            $count_result_va = count($result_va);

            //membuat array data_input
            $data_input = [];
            for ($i = 0; $i < $count_result_va; $i++) {
                $bank    = $result_va[$i]->bankCd;
                $va_bank = $result_va[$i]->vacctNo;

                //mengecek ketersedian va di database
                $cek_data = UserVa::where('nova', $va_bank)->get();
                if (count($cek_data) == 0) {
                    $data_input[$i] = ['nohp' => $nohp, 'bank' => $bank, 'nova' => $va_bank];
                }
            }

            //insert multiple data
            $input_user_va = DB::transaction(function () use ($data_input) {
                $store = UserVa::insert($data_input);
            });
            echo "Generate Va Sukses" . PHP_EOL;
        } else {
            echo "generate va error|" . PHP_EOL;
        }
    }
}
