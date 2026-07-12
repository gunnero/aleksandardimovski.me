<?php

namespace App\Http\Controllers;

use App\Content\PortfolioContent;

final class ProjectController extends Controller
{
    public function index(PortfolioContent $content)
    {
        return view('projects.index', ['projects' => $content->projects()]);
    }

    public function show(string $project, PortfolioContent $content)
    {
        abort_unless($item = $content->project($project), 404);

        return view('projects.show', ['project' => $item]);
    }
}
