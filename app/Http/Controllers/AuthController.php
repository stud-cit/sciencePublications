<?php

namespace App\Http\Controllers;

use App\Models\Authors;
use Illuminate\Http\Request;
use Session;
use Config;

class AuthController extends ASUController {
    protected $cabinet_api = "https://cabinet.sumdu.edu.ua/api/";
    protected $cabinet_service = "https://cabinet.sumdu.edu.ua/index/service/";
    protected $cabinet_service_token;

    function __construct() {
      $this->cabinet_service_token = config('app.token');
    }

    function checkUser(Request $request) {
        $personCabinet = json_decode(file_get_contents($this->cabinet_api . 'getPerson?key=' . $request->session()->get('key') . '&token=' . $this->cabinet_service_token), true);
        if ($personCabinet['status'] == 'OK') {
            $userModel = Authors::with('notifications')->where("name", $personCabinet['result']['surname'] . " " . $personCabinet['result']['name'] . " " . $personCabinet['result']['patronymic']);
            if ($userModel->exists()) {
                $user = $userModel->first();

                $kod_div = $user['department_code'] ? $user['department_code'] : $user['faculty_code'];

                $division = $this->getUserDivision($kod_div)->original;
                $user['department'] = $division['department'] ? $division['department']['NAME_DIV'] : null;
                $user['faculty'] = $division['institute'] ? $division['institute']['NAME_DIV'] : null;
                return response()->json([
                    "status" => "register",
                    "user" => $user
                ]);
            } else {
                return response()->json([
                    "status" => "login",
                    "user" => $personCabinet['result']
                ]);
            }
        } else {
            return response()->json([
                "status" => "unauthorized"
            ]);
        }
    }

    function logout(Request $request) {
        $request->session()->flush();
        return response('ok', 200);
    }

    function register(Request $request) {
        $personCabinet = json_decode(file_get_contents($this->cabinet_api . 'getPersonInfo?key=' . $request->session()->get('key') . '&token=' . $this->cabinet_service_token), true);

        if(!Authors::where("guid", $personCabinet['result']['guid'])->exists()) {
            $data = $request->all();
            $data['name'] = $personCabinet['result']['surname'] . " " . $personCabinet['result']['name'] . " " . $personCabinet['result']['patronymic'];
            $data['job'] = "СумДУ";
            $data['country'] = "Україна";
            $data['guid'] = $personCabinet['result']['guid'];
            $data['token'] = $personCabinet['result']['token'];
            $data['test_data'] = json_encode($personCabinet['result']);

            $kod_div = null;

            $isStudent = false;

            if(isset($personCabinet['result']['info1'])) {
                foreach ($personCabinet['result']['info1'] as $key => $value) {
                    if($value['KOD_STATE'] == 1 && $value['CATEG'] == 2 && $value['KOD_LEVEL'] == 8) {
                        $data['categ_1'] = $value['CATEG'];
                        $kod_div = $value['KOD_DIV'];
                        $data['academic_code'] = $value['NAME_GROUP'];
                        $kod_div = $this->getAspirantDepartment($personCabinet['result']['guid']);
                        $isStudent = true;
                    }
                }
            }

            if(isset($personCabinet['result']['info2']) && !$isStudent) {
                foreach ($personCabinet['result']['info2'] as $key => $value) {
                    if(($value['KOD_SYMP'] == 1 || $value['KOD_SYMP'] == 5) && ($value['KOD_STATE'] == 1 || $value['KOD_STATE'] == 2 || $value['KOD_STATE'] == 3)) {
                        $data['categ_2'] = $value['CATEG'];
                        $kod_div = $value['KOD_DIV'];
                    }
                }
            }

            $division = $this->getUserDivision($kod_div)->original;
            $data['department_code'] = $division['department'] ? $division['department']['ID_DIV'] : null;
            $data['faculty_code'] = $division['institute'] ? $division['institute']['ID_DIV'] : null;

            $userModel = new Authors();
            $newUser = $userModel->create($data);

            $newUser['department'] = $division['department'] ? $division['department']['NAME_DIV'] : null;
            $newUser['faculty'] = $division['institute'] ? $division['institute']['NAME_DIV'] : null;

            $request->session()->put('person', $newUser);
            return response()->json('ok', 200);
        } else {
            return response()->json([
                "message" => "Користувач вже зареєстрований в системі"
            ]);
        }
    }

    function index(Request $request) {
        $this->mode($request);

        if(!isset($request->key)) {
            return view('app', [
                "status" => "unauthorized"
            ]);
        }
        $personCabinet = json_decode(file_get_contents($this->cabinet_api . 'getPersonInfo?key=' . $request->key . '&token=' . $this->cabinet_service_token), true);
        if ($personCabinet['status'] == 'OK') {
            $request->session()->put('key', $request->key);
            $userModel = Authors::where("guid", $personCabinet['result']['guid'])->where("name", $personCabinet['result']['surname'] . " " . $personCabinet['result']['name'] . " " . $personCabinet['result']['patronymic']);
            if($userModel->exists()) {
                $person = $userModel->first();
                $person->name = $personCabinet['result']['surname'] . " " . $personCabinet['result']['name'] . " " . $personCabinet['result']['patronymic'];
                $person->academic_code = null;
                $person->categ_1 = null;
                $person->add_user_id = null;
                $person->test_data = json_encode($personCabinet['result']);

                $kod_div = null;

                $isStudent = false;

                if(isset($personCabinet['result']['info1'])) {
                    foreach ($personCabinet['result']['info1'] as $key => $value) {
                        if($value['KOD_STATE'] == 1 && $value['CATEG'] == 2 && $value['KOD_LEVEL'] == 8) {
                            $person->categ_1 = $value['CATEG'];
                            $person->academic_code = $value['NAME_GROUP'];
                            $kod_div = $this->getAspirantDepartment($personCabinet['result']['guid']);
                            $isStudent = true;
                        }
                    }
                }

                if(isset($personCabinet['result']['info2']) && !$isStudent) {
                    foreach ($personCabinet['result']['info2'] as $key => $value) {
                        if(($value['KOD_SYMP'] == 1 || $value['KOD_SYMP'] == 5) && ($value['KOD_STATE'] == 1 || $value['KOD_STATE'] == 2 || $value['KOD_STATE'] == 3)) {
                            $person->categ_2 = $value['CATEG'];
                            $kod_div = $value['KOD_DIV'];
                        }
                    }
                }

                if(!$person['custom_divisions']) {
                    $division = $this->getUserDivision($kod_div)->original;
                    $person->department_code = $division['department'] ? $division['department']['ID_DIV'] : null;
                    $person->faculty_code = $division['institute'] ? $division['institute']['ID_DIV'] : null;
                }

                $userModel->update($person->toArray());

                $request->session()->put('person', $person);
                return view('app', [
                    "status" => "register",
                    "user" => $person
                ]);
            } else {
                return view('app', [
                    "status" => "login"
                ]);
            }
        } else {
            return view('app', [
                "status" => "unauthorized"
            ]);
        }
    }

    function mode($request) {
        $info = 'Інформаційний сервіс «Наукові публікації» дозволить Вам зручно вести облік Ваших наукових та науково-методичних публікацій'; // Service description
        $icon = public_path() . "/service.png"; // Service icon (48x48)
        $mask = 13; // Service modes 3,2,0 (1101 bits)

        $mode = !empty($request->mode) ? $request->mode : 0;

        // В зависимости от режима (mode) возвращаем или иконку, или описание, или специальный заголовок
        switch($mode) {
            case 0:
                break;
            case 2:
                header('Content-Type: image/png');
                readfile($icon);
                exit;
            case 3:
                echo $info;
                exit;
            case 100;
                header('X-Cabinet-Support: ' . $mask);
            default:
                exit;
        }
    }
}
