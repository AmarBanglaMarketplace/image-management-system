<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Category;
use App\Models\CommitteeMember;
use App\Models\Event;
use App\Models\Gallery;
use App\Models\News;
use App\Models\Notice;
use App\Models\Page;
use App\Models\Player;
use App\Models\Post;
use App\Models\Result;
use App\Models\Section;
use App\Models\Setting;
use App\Models\Slider;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Exception;
use Illuminate\Http\Request;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Frontend Controller for public-facing website pages.
 *
 * Handles rendering of all frontend views with necessary data fetching
 * (homepage, pages, news, blogs, events, players, galleries, etc.).
 */
class FrontendController extends Controller {}
