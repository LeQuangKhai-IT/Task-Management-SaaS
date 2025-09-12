<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Card;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/search",
     *     summary="Search resources",
     *     tags={"Search"},
     *     @OA\Parameter(
     *         name="query", in="query", required=true,
     *         @OA\Schema(type="string"), description="Keyword to search"
     *     ),
     *     @OA\Parameter(
     *         name="type", in="query", required=false,
     *         @OA\Schema(type="string", enum={"board","card","user","workspace"}),
     *         description="Filter by resource type"
     *     ),
     *     @OA\Response(response=200, description="Search results (array or object depending on type)")
     * )
     */
    public function index(Request $request)
    {
        $query = $request->input('query');
        $type  = $request->input('type');

        switch ($type) {
            case 'board':
                return Board::where('name', 'like', "%$query%")->get();
            case 'card':
                return Card::where('name', 'like', "%$query%")->get();
            case 'user':
                return User::where('name', 'like', "%$query%")->get();
            case 'workspace':
                return Workspace::where('name', 'like', "%$query%")->get();
            default:
                // search all
                return [
                    'boards' => Board::where('name', 'like', "%$query%")->get(),
                    'cards' => Card::where('name', 'like', "%$query%")->get(),
                    'users' => User::where('name', 'like', "%$query%")->get(),
                    'workspaces' => Workspace::where('name', 'like', "%$query%")->get(),
                ];
        }
    }
}
