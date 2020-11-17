<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Authors;
use App\Models\Users;
use App\Models\Roles;
use App\Models\Notifications;
use Carbon\Carbon;
use Session;

class AuthorsController extends ASUController
{
    protected $asu_key = 'eRi1FIAppqFDryG2PFaYw75S1z4q2ZoG';
    
    function updateUsers() {
        $model = Authors::get();
        $mode = 1;
        $categ = 'categ2';
        $id = 4;
        $getPersons = file_get_contents('https://asu.sumdu.edu.ua/api/getContingents?key='.$this->asu_key.'&mode='.$mode.'&'.$categ.'='.$id);
        $getPersons = json_decode($getPersons, true);

        foreach ($model as $key => $value) {
            if($value['categ_2'] == 2) {
                $userId = array_search($value['guid'], array_column($getPersons['result'], 'ID_FIO'));
                $division = $this->getUserDivision($getPersons['result'][$userId]['KOD_DIV'])->original;
                $modelUser = Authors::find($value['id']);
                $modelUser->update([
                    "department_code" => $division['department'] ? $division['department']['ID_DIV'] : null,
                    "faculty_code" => $division['institute'] ? $division['institute']['ID_DIV'] : null
                ]);
            }
        }
        return response('ok', 200);
    }

    // authors
    function get(Request $request) {
        $data = [];
        $model = Authors::with('role')->orderBy('created_at', 'DESC');
        if($request->session()->get('person')['roles_id'] == 3) {
            $model->where('faculty_code', $request->session()->get('person')['faculty_code'])
                ->where('categ_1', "!=", 1)
                ->where('guid', "!=", null);
        }
        if($request->session()->get('person')['roles_id'] == 2) {
            $model->where('department_code', $request->session()->get('person')['department_code'])
                ->where('categ_1', "!=", 1)
                ->where('guid', "!=", null);
        }

        if($request->name != '') {
            $model->where('name', 'like', "%".$request->name."%");
        }

        if($request->faculty_code != '') {
            $model->where('faculty_code', $request->faculty_code);
        }

        if($request->department_code != '') {
            $model->where('department_code', $request->department_code);
        }

        if($request->all == 'true') {
            $data = $model->get();
        } else {
            $data = $model->where('categ_1', "!=", 1)->where('guid', "!=", null)->get();
        }

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

    // authors All (admin)
    function getAll(Request $request) {
        $data = Authors::with('role')->get();
        foreach ($data as $key => $value) {
            $value['position'] = $this->getPosition($value);
            $kod_div = $value['department_code'] ? $value['department_code'] : $value['faculty_code'];
            $division = $this->getUserDivision($kod_div)->original;
            $value['department'] = $division['department'] ? $division['department']['NAME_DIV'] : null;
            $value['faculty'] = $division['institute'] ? $division['institute']['NAME_DIV'] : null;
        }
        return response()->json($data);
    }

    // cabinet users (add publication page)
    function getPersons($categ, $id) {
        
        $mode = 1;
        $getPersons = file_get_contents('https://asu.sumdu.edu.ua/api/getContingents?key='.$this->asu_key.'&mode='.$mode.'&'.$categ.'='.$id);
        $getPersons = json_decode($getPersons, true);
        $result = [];
        foreach ($getPersons['result'] as $key => $value) {
            array_push($result, [
                "name" => $value['F_FIO'] . " " . $value['I_FIO'] . " " . $value['O_FIO'],
                "kod_div" => $value['KOD_DIV'],
                "name_div" => ($categ == 'categ1' && $id == 4) || $categ == 'categ2' ? $value['NAME_DIV'] : $value['NAME_GROUP'],
                "categ_1" => $value['CATEG_1'],
                "categ_2" => $value['CATEG_2'],
                "job" => ($categ == 'categ1' && $id == 2) ? null : "СумДУ",
                "country" => "Україна",
                "academic_code" => ($categ == 'categ1' && $id == 2) ? $value['NAME_GROUP'] : null,
                "guid" => $value['ID_FIO']
            ]);
        }
        return response()->json($result);
    }

    // profile (Сторінка профілю)
    function profile(Request $request) {
        $data = Authors::with('role')->find($request->session()->get('person')['id']);
        $data->position = $this->getPosition($data);

        $kod_div = $data->department_code ? $data->department_code : $data->faculty_code;

        $division = $this->getUserDivision($kod_div)->original;
        $data->department = $division['department'] ? $division['department']['NAME_DIV'] : null;
        $data->faculty = $division['institute'] ? $division['institute']['NAME_DIV'] : null;

        return response()->json($data);
    }

    // updateProfile (Оновлення информації профілю)
    function updateProfile(Request $request) {
        $data = $request->all();
        Authors::find($request->session()->get('person')['id'])->update($data);
        $user = Authors::find($request->session()->get('person')['id']);
        return response()->json($user);
    }

    // getId (Користувач по id)
    function getId($id) {
        $data = Authors::with(
            'publications.publication.authors.author',
            'publications.publication.publicationType',
            'publications.publication.scienceType',
            'role'
        )->find($id);
        $data->position = $this->getPosition($data);

        $kod_div = $data->department_code ? $data->department_code : $data->faculty_code;

        $division = $this->getUserDivision($kod_div)->original;
        $data->department = $division['department'] ? $division['department']['NAME_DIV'] : null;
        $data->faculty = $division['institute'] ? $division['institute']['NAME_DIV'] : null;

        foreach ($data['publications'] as $key => $publication) {
            $publication['publication']['date'] = Carbon::parse($publication['publication']['created_at'])->format('d.m.Y');
        }
        return response()->json($data);
    }

    function getOtherAuthorNames() {
        $data = Authors::select('name', 'job')->where("guid", null)->get();
        return response()->json($data);
    }

    // додання автора не з СумДУ
    function postAuthor(Request $request) {
        if(!Authors::where("guid", null)->where("name", "like", $request->name)->where("job", "like", $request->job)->exists()) {
            $model = new Authors();
            $data = $request->all();
            $response = $model->create($data);
            return response()->json([
                "status" => "ok",
                "user" => $response
            ]);
        } else {
            return response()->json(["status" => "error"]);
        }
    }

    // додання автора з СумДУ
    function postAuthorSSU(Request $request) {
        if(!Authors::where("guid", $request->guid)->where("name", "like", $request->name)->exists()) {
            $model = new Authors();
            $data = $request->all();

            $division = $this->getUserDivision($request->kod_div)->original;
            $data['department_code'] = $division['department'] ? $division['department']['ID_DIV'] : null;
            $data['faculty_code'] = $division['institute'] ? $division['institute']['ID_DIV'] : null;
            
            $response = $model->create($data);

            $response['department'] = $division['department'] ? $division['department']['NAME_DIV'] : null;
            $response['faculty'] = $division['institute'] ? $division['institute']['NAME_DIV'] : null;

            return response()->json([
                "status" => "ok",
                "user" => $response
            ]);
        } else {
            return response()->json(["status" => "error"]);
        }
    }

    // updateAuthor
    function updateAuthor(Request $request, $id) {
        $notificationText = "";
        $data = $request->all();
        $model = Authors::with('role')->find($id);

        $roles = [
            1 => "Автор",
            2 => "Модератор кафедрального рівня",
            3 => "Модератор інститутського або факультетського рівня",
            4 => "Адміністратор"
        ];
        $notificationText .= $this->notification($data, $model, "roles_id", "роль", $roles);
        $notificationText .= $this->notification($data, $model, "five_publications", "5 або більше публікацій у періодичних виданнях в Scopus та/або WoS");
        $notificationText .= $this->notification($data, $model, "scopus_autor_id", "Індекс Гірша БД Scopus");
        $notificationText .= $this->notification($data, $model, "h_index", "Індекс Гірша БД WoS");
        $notificationText .= $this->notification($data, $model, "scopus_researcher_id", "Research ID");
        $notificationText .= $this->notification($data, $model, "orcid", "ORCID");

        if($notificationText != "") {
            $notificationText = "Користувач <a href=\"/user/". $request->session()->get('person')['id'] ."\">" . $request->session()->get('person')['name'] . "</a> вніс наступні зміни в Ваш профіль:<br>" . $notificationText;
            Notifications::create([
                "autors_id" => $id,
                "text" => $notificationText
            ]);
        }
        $model->update($data);
        return response('ok', 200);
    }

    function notification($data, $model, $key, $text, $arr = null) {
        if($arr) {
            if($data[$key] && !$model->$key) {
                return "додано ".$text.": " . $arr[$data[$key]] . ";<br>";
            }
            if(($data[$key] != $model->$key) && $data[$key] && $model->$key) {
                return "змінено ".$text.": " . $arr[$model->$key] . " на " . $arr[$data[$key]] . ";<br>";
            }
            if(!$data[$key] && $model->$key) {
                return "видалено ".$text.";<br>";
            }
        } else {
            if($data[$key] && !$model->$key) {
                return "додано ".$text.": " . $data[$key] . ";<br>";
            }
            if(($data[$key] != $model->$key) && $data[$key] && $model->$key) {
                return "змінено ".$text.": " . $model->$key . " на " . $data[$key] . ";<br>";
            }
            if(!$data[$key] && $model->$key) {
                return "видалено ".$text.";<br>";
            }
        }
    }  

    // deleteAuthor
    function deleteAuthor(Request $request) {
        foreach ($request->users as $key => $user) {
            Authors::find($user['id'])->delete();
        }
        return response('ok', 200);
    }

    // getRoles
    function getRoles() {
        $data = Roles::get();
        return response()->json($data);
    }

    //notifications
    function getNotifications($autors_id) {
        $data = Notifications::where('autors_id', $autors_id)->orWhere('autors_id', null)->orderBy('created_at', 'DESC')->get();
        foreach ($data as $key => $value) {
            $value['date'] = Carbon::parse($value->created_at)->format('d.m.Y H:i');
        }
        return response()->json($data);
    }
    function postNotifications(Request $request, $autors_id) {
        $model = new Notifications();
        $data = $request->all();
        $data["autors_id"] = $autors_id;
        $model->create($data);
        return response('ok', 200);
    }
    function editNotifications(Request $request, $id, $autors_id) {
        Notifications::find($id)->update([
            "status" => $request->status
        ]);
        return response('ok', 200);
    }

    // Визначити посаду
    function getPosition($data) {
        $result = "";
        if($data->categ_1 == 1) {
            $result = "Студент";
        } elseif ($data->categ_1 == 2) {
            $result = "Аспірант";
        } elseif ($data->categ_2 == 1) {
            $result = "Співробітник";
        } elseif ($data->categ_2 == 2) {
            $result = "Викладач";
        } elseif ($data->categ_2 == 3) {
            $result = "Менеджер";
        } else {
            $result = $data->job;
        }
        return $result;
    }

    // Визначення віку користувача
    function calculateAge($birthday) {
        $birthday_timestamp = strtotime($birthday);
        $age = date('Y') - date('Y', $birthday_timestamp);
        if (date('md', $birthday_timestamp) > date('md')) {
            $age--;
        }
        return $age;
      }
}
