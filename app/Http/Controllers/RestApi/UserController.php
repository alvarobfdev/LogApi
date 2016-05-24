<?php

namespace App\Http\Controllers\RestApi;

use App\RestApiModels\User;
use Illuminate\Http\Request;

use App\Http\Requests;

class UserController extends Controller
{



    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            if (!$request->has("user")) {
                return response('Bad Request 1.', 400);
            }
            if (!$this->checkObligatoryFields($request->get("user"), User::$obligatory_post_fields)) {
                return response('Bad Request 2.', 400);
            }

            $object = new User();
            $this->addObligatoryFields($request->get("user"), User::$obligatory_post_fields, $object);
            $object->auth_key = str_random(60);
            $object->auth_token = str_random(60);
            $object->save();
            return $object->toJson();
        }
        catch(\Exception $e) {
            return response('Internal Server Error.', 500);

        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
