<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeRequest;
use App\Models\Company;
use App\Models\employee;
use Exception;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $default_lang=get_default_lang();
        $employees=Employee::where('translation_lang',$default_lang)->Selection()->paginate(5);
 
        return view('admin.employee.index',compact('employees'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //company variable definde in AppServer Provider to share all views
             return view('admin.employee.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(EmployeeRequest $request)
    {
        try
        {
             
         $employee=collect($request->employee);
         $filter=$employee->filter(function($value,$key){
            return $value['translation_lang']==get_default_lang();
         });

          $default_employee=array_values($filter->all())[0];

           $default_employee_id=employee::insertGetId([
                'translation_lang'=>$default_employee['translation_lang'],
                'translation_of'=>0,
                'name'=>$default_employee['name'],
                'phone'=>$request->phone,
                'email'=>$request->email,
                'company_id'=>$request->company_id,
                'active'=>0,
                'created_at' => date("Y-m-d H:i:s", strtotime('now')),
                'updated_at'=>date("Y-m-d H:i:s", strtotime('now'))
            ]);
        
            //Repeat the same line above for arabic ,language but here return any other Language rather than default
                $employee_other_default_lang=$employee->filter(function($value,$key)
                {
                return $value['translation_lang']!=get_default_lang();
                });

            //check if the save categories other Languages exist and have data

            if(isset($employee_other_default_lang)&&$employee_other_default_lang->count())
            {
                //create empty array,we create array and make foreach just for performance
                $employee_arr=[];

                foreach($employee_other_default_lang as $employee)
                {
                    $employee_arr[]=[
                        'translation_lang'=>$employee['translation_lang'],
                        'translation_of'=>$default_employee_id,
                        'name'=>$employee['name'],
                        'phone'=>$request->phone,
                        'email'=>$request->email,
                        'company_id'=>$request->company_id,
                        'active'=>0,
                        'created_at' => date("Y-m-d H:i:s", strtotime('now')),
                        'updated_at'=>date("Y-m-d H:i:s", strtotime('now'))
                    ];
                }
                //save the another Language
                employee::insert($employee_arr);
            }
            return redirect()->route('employee.index')->with('successs','Employee added successfully');
        }
        catch(Exception $ex)
        {
            return redirect()->route('employee.index')->with('error','something error');
        }
            
    }
    
    

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function show(employee $employee)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try
        {
            $employee=employee::with('trans_employee')->Selection()->find($id);

            if(!$employee)
            {
                return redirect()->route('employee.index')->with('error','?????? ???????????? ?????? ?????????? ???? ???????? ???????? ??????????');
            }

            return view('admin.employee.edit',compact('employee'));

        }
        catch(Exception $ex)
        {
            return redirect()->route('employee.index')->with('error','?????? ?????? ???? ???????? ???????????????? ??????????');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function update(EmployeeRequest $request, $id)
    {
        
         
        try
        {
               $employee=employee::Selection()->find($id);
            if(!$employee)
            {
                return redirect()->route('employee.index')->with('error','?????? ???????????? ?????? ???????????? ???? ???????? ???????? ????????????');
            }

            $first_employee=array_values($request->employee)[0];
              employee::where('id',$id)->update([
                'name'=>$first_employee['name'],
                'phone'=>$request->phone,
                'email'=>$request->email,
                'company_id'=>$request->company_id
            ]);

            return redirect()->route('employee.index')->with('success','???? ?????????? ?????????????? ???????????? ??????????');



        }
        catch(Exception $ex)
        {
            return $ex;
             return redirect()->route('employee.index')->with('error','?????? ?????? ???? ???????? ???????????? ??????????');
        }
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try 
        {
            $employees=employee::find($id);
            if(!$employees)
            {
                return redirect()->route('employee.index')->with('error','?????? ???????????? ?????? ??????????');

            }
          $employees->delete();
          $employees->trans_employee()->delete();




          return redirect()->route('employee.index')->with('success','???? ?????? ???????????? ??????????');


      } catch (Exception $ex)
      {

          //return $ex;
          //throw $th;
          //DB::rollBack(); No need rollback because there is no more than one transaction in DB

          return redirect()->route('employee.index')->with('error','?????? ?????? ???? ?????????? ???????????????? ??????????');
      }
    }
    public function ChangeStatus($id)
    {
        
        try
        {
             $employee=employee::find($id);

            if(!$employee)
                return redirect()->route('employee.index')->with('error','???????? ?????? ???????????? ?????? ??????????');

                //check if status is not active will become active else will become not active

                 $active=$employee->active==0?1:0;

                 //update the active column in Vendors Table
                 $employee->update(['active'=>$active]);

                 return redirect()->route('employee.index')->with('success','???? ?????????? ???????? ???????????? ??????????');

        }
        catch(Exception $ex)
        {
            return redirect()->route('employee.index')->with('error','?????? ?????? ???? ?????????? ???????????????? ??????????');
        }
    }


}
