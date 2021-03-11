<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Authors;
use App\Models\Users;
use App\Models\Roles;
use App\Models\Notifications;
use App\Models\Publications;
use App\Models\AuthorsPublications;
use Session;
use Config;

use App\Http\Traits\AuthorTrait;

class DevController extends ASUController {

    use AuthorTrait;

    protected $cabinet_api = "https://cabinet.sumdu.edu.ua/api/";
    protected $asu_key = "eRi1FIAppqFDryG2PFaYw75S1z4q2ZoG";
    protected $asu_sumdu_api = "http://asu.sumdu.edu.ua/api/getDivisions?key=";
    protected $cabinet_service_token = "TNWcmzpZ";

    function saveTable() {
      $data = json_decode(file_get_contents(public_path() . '/json/authorsHasPublications.json'), TRUE);
  
      foreach (array_slice($data, 9379, (count($data) - 9379)) as $key => $value) {
        AuthorsPublications::create($value);
      }
      return response('ok', 200);
    }


    function updateUsers() {
        $data = Authors::where('test_data', '!=', null)->get();
        foreach ($data as $key => $user) {
            if(isset($user['test_data'])) {
                $isStudent = false;
                $getPersons = json_decode($user['test_data'], true);
                if(isset($getPersons['info1'])) {
                    foreach ($getPersons['info1'] as $k => $v) {
                        if($v['KOD_STATE'] == 1) {
                            $newData['categ_1'] = $v['CATEG'];
                            if($v['KOD_LEVEL'] == 3 || $v['KOD_LEVEL'] == 5 || $v['KOD_LEVEL'] == 8) {
                                $user['name_div'] = $v['NAME_DIV'];
                            }
                            $isStudent = true;
                        }
                    }
                }
                if(isset($getPersons['info2']) && !$isStudent) {
                    foreach ($getPersons['info2'] as $k => $v) {
                        if(($v['KOD_STATE'] == 1 || $v['KOD_STATE'] == 2 || $v['KOD_STATE'] == 3) && ($v['KOD_SYMP'] == 1 || $v['KOD_SYMP'] == 5)) {
                            $user['job'] = "СумДУ";
                            $user['job_type_id'] = 5;
                            $user['categ_2'] = $v['CATEG'];
                            $user['name_div'] = $v['NAME_DIV'];
                        }
                    }
                }
                Authors::find($user['id'])->update($user->toArray());
            }
        }
        return response('ok', 200);
    }

    function getUsers() {
        $data = Authors::where('test_data', null)
        ->where('token', null)
        ->count();

        // $key = "g0uCOJAyanWpSflTnr69IZpQ9ptJSrmfI3rFARptPmetc7v83ja2";

        // $test = [];

        // foreach ($data as $key => $value) {
        //     $getPersons = json_decode(file_get_contents($this->cabinet_api . 'getPersons?key=g0uCOJAyanWpSflTnr69IZpQ9ptJSrmfI3rFARptPmetc7v83ja2&token=TNWcmzpZ&search=' . urlencode($value['name'])), true);
        //     array_push($test, $getPersons['result']);
        // }


        return response()->json($data);
    }


    function getUserId($id) {
        $data = Authors::find($id);
        $data['test_data'] = json_decode($data['test_data'], true);
        return response()->json($data['test_data']['info1']);
    }


    function test() {
        //$data = Publications::where('publication_type_id', 9)->where('name_conference', null)->get();
        $arr = [];
        $data = Publications::with('authors')->get();
        foreach ($data as $key => $value) {
            if(count($value['authors']) == 0) {
                array_push($arr, $value['id']);
            }
        }
        return response()->json($arr);
    }

    function updateCabinetInfo($user_id) {

        $key = "iYWRu2UbsiXZ3CJUIy77HZ8A2tOnhp3eI2904F0Ih95wJPZfBT42";

        $model = Authors::find($user_id);


        if(isset($model['test_data'])) {
            $getPersons = json_decode($model['test_data'], true);
            $person = [
                "department_code" => null,
                "faculty_code" => null,
                "categ_1" => 0,
                "categ_2" => 0,
                "academic_code" => null
            ];
            $isStudent = false;
            $kod_div = null;
            if(isset($getPersons['info1'])) {
                foreach ($getPersons['info1'] as $key => $value) {
                    if($value['KOD_STATE'] == 1 && $value['CATEG'] == 2 && $value['KOD_LEVEL'] == 8) {
                        $person['categ_1'] = $value['CATEG'];
                        $person['academic_code'] = $value['NAME_GROUP'];
                        $kod_div = $this->getAspirantDepartment($getPersons['guid']);
                        $isStudent = true;
                    }
                }
            }

            if(isset($getPersons['info2']) && !$isStudent) {
                foreach ($getPersons['info2'] as $key => $value) {
                    if(($value['KOD_SYMP'] == 1 || $value['KOD_SYMP'] == 5) && ($value['KOD_STATE'] == 1 || $value['KOD_STATE'] == 2 || $value['KOD_STATE'] == 3)) {
                        $person['categ_2'] = $value['CATEG'];
                        $kod_div = $value['KOD_DIV'];
                    }
                }
            }


            if($kod_div) {
                $division = $this->getUserDivision($kod_div, $this->getAllDivision());
                $person['department_code'] = $division['department'] ? $division['department']['ID_DIV'] : null;
                $person['faculty_code'] = $division['institute'] ? $division['institute']['ID_DIV'] : null;
            }


            $model->update($person);

            return response()->json([
                'status' => 'ok'
            ]);
        } else {
            $getPersons = json_decode(file_get_contents($this->cabinet_api . 'getPersons?key=' . $key . '&token=' . $this->cabinet_service_token . '&search=' . urlencode($model['name'])), true);

            if($getPersons['status'] == 'OK' && count($getPersons['result']) > 0) {
                $mode = 1;
                $getPersons = array_shift($getPersons['result']);

                $getContingents = json_decode(file_get_contents('https://asu.sumdu.edu.ua/api/getContingents?key=' . $this->asu_key . '&mode=' . $mode . '&categ1=' . $getPersons['categ1'] . '&categ2=' . $getPersons['categ2']), true);

                return response()->json($getContingents);

                if($getContingents['status'] == 'OK') {

                    $person = [];

                    $aspirant = array_filter($getContingents['result'], function($value) use ($model) {
                        return ($value['F_FIO'] . ' ' . $value['I_FIO'] . ' ' . $value['O_FIO']) == $model['name'] && $value['ID_FIO'] == $model['guid'] && $value['CATEG_1'] == 2 && ($value['KOD_LEVEL'] == 8 || $value['KOD_LEVEL'] == 5);
                    });

                    if(count($aspirant) == 0) {
                        $anotherUser = array_filter($getContingents['result'], function($value) use ($model) {
                            return ($value['F_FIO'] . ' ' . $value['I_FIO'] . ' ' . $value['O_FIO']) == $model['name'] && $value['ID_FIO'] == $model['guid'];
                        });
                        $person = array_shift($anotherUser);
                    } else {
                        $person = array_shift($aspirant);
                        if($person['KOD_LEVEL'] == 8) {
                            $person['KOD_DIV'] = $this->getAspirantDepartment($person['ID_FIO']);
                        }
                    }

                    $division = $this->getUserDivision($person['KOD_DIV'], $this->getAllDivision());
                    $person['DEPARTMENT_CODE'] = $division['department'] ? $division['department']['ID_DIV'] : null;
                    $person['FACULTY_CODE'] = $division['institute'] ? $division['institute']['ID_DIV'] : null;

                    $model->update([
                        "name" => $person['F_FIO'] . ' ' . $person['I_FIO'] . ' ' . $person['O_FIO'],
                        "faculty_code" => $person['FACULTY_CODE'],
                        "department_code" => $person['DEPARTMENT_CODE'],
                        "academic_code" => $person['NAME_GROUP'],
                        "categ_1" => $person['CATEG_1'],
                        "categ_2" => $person['CATEG_2'],
                    ]);

                    return response()->json([
                        'status' => 'ok'
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 'error'
                ]);
            }
        }
    }

    function checkStudent() {
        $data = Authors::select('name', 'academic_code', 'faculty_code', 'department_code')->orderBy('name', 'ASC')->where('categ_1', 1)->update([
            'faculty_code' => null,
            'department_code' => null
        ]);
        return response('ok', 200);
    }

    function checkSSUNotWork() {
        // $data = Authors::select('name', 'guid', 'faculty_code', 'department_code', 'job')->orderBy('name', 'ASC')->where('job', 'СумДУ')->where('guid', null)->update([
        //     "job" => "СумДУ (Не працює)"
        // ]);
        $data = Authors::select('name', 'guid', 'faculty_code', 'department_code', 'job')->orderBy('name', 'ASC')->where('job', 'like', '%Сумський державний університет%')->get();
        return response()->json($data);
    }

    // authors All (admin)
    function getAll(Request $request) {
        $data = Authors::with('role')->orderBy('name', 'ASC')->get();

        $divisions = $this->getAllDivision()->original;

        foreach ($data as $key => $value) {
            $value['position'] = $this->getPosition($value);

            $kod_div = $value['department_code'] ? $value['department_code'] : $value['faculty_code'];

            $sectionId = array_search($kod_div, array_column($divisions, 'ID_DIV'));
            $departmentId = array_search($divisions[$sectionId]['ID_PAR'], array_column($divisions, 'ID_DIV'));
            $facultyId = array_search($divisions[$departmentId]['ID_PAR'], array_column($divisions, 'ID_DIV'));
            if(!$departmentId) {
                $departmentId = $sectionId;
                $sectionId = null;
            }
            if(!$facultyId) {
                $facultyId = $departmentId;
                $departmentId = $sectionId;
                $sectionId = null;
            }

            $value['department'] = $departmentId ? $divisions[$departmentId]['NAME_DIV'] : null;
            $value['faculty'] = $facultyId ? $divisions[$facultyId]['NAME_DIV'] : null;
        }
        return response()->json($data);
    }
}
