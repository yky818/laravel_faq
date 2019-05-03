<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Question;
use App\Vote;
use Illuminate\Support\Facades\Auth;

class QuestionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
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

        $question = new Question;
        $edit = FALSE;
        return view('questionForm', ['question' => $question,'edit' => $edit  ]);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->validate([
            'body' => 'required|min:5',
        ], [

            'body.required' => 'Body is required',
            'body.min' => 'Body must be at least 5 characters',

        ]);
        $input = request()->all();

        $question = new Question($input);
        $question->user()->associate(Auth::user());
        $question->save();

        return redirect()->route('questions.show', ['id' => $question->id]);
        // return redirect()->route('home')->with('message', 'Question added successfully');

    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Question $question)
    {
        return view('question')->with('question', $question);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Question $question)
    {
        $edit = TRUE;
        return view('questionForm', ['question' => $question, 'edit' => $edit ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Question $question)
    {

        $input = $request->validate([
            'body' => 'required|min:5',
        ], [

            'body.required' => 'Body is required',
            'body.min' => 'Body must be at least 5 characters',

        ]);

        $question->body = $request->body;
        $question->save();

        return redirect()->route('questions.show',['question_id' => $question->id])->with('message', 'Question updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Question $question)
    {
        $question->delete();
        return redirect()->route('home')->with('message', 'Question Deleted successfully');

    }

    /**
     * It will list all user questions
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function userQuestions()
    {
        $user = Auth::user();
        $questions = $user->questions()->paginate(6);
        return view('userquestions')->with('questions', $questions);
    }

    /**
     * It will list all user questions
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function addRemoveVote(Request $request)
    {
        $request->validate([
            'qid' => 'required',
        ], [

            'qid.required' => 'question id is required',
        ]);

        $user = Auth::user();
        $isVote = Vote::where('question_id',$request->qid)->where('user_id',$user->id)->first();
        if($isVote){
            $isVote->delete();
            $voteStatus = 0;
        }else{
            $table = new Vote();
            $table->question_id = $request->qid;
            $table->user_id = $user->id;
            $table->save();
            $voteStatus = 1;
        }
        return response()->json(['voteStatus' => $voteStatus]);
    }
}
