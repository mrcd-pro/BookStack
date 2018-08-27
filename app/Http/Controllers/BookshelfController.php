<?php namespace BookStack\Http\Controllers;

use Activity;
use BookStack\Book;
use BookStack\Repos\EntityRepo;
use BookStack\Repos\UserRepo;
use BookStack\Services\ExportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Views;

class BookshelfController extends Controller
{

    protected $entityRepo;
    protected $userRepo;
    protected $exportService;

    /**
     * BookController constructor.
     * @param EntityRepo $entityRepo
     * @param UserRepo $userRepo
     * @param ExportService $exportService
     */
    public function __construct(EntityRepo $entityRepo, UserRepo $userRepo, ExportService $exportService)
    {
        $this->entityRepo = $entityRepo;
        $this->userRepo = $userRepo;
        $this->exportService = $exportService;
        parent::__construct();
    }

    /**
     * Display a listing of the book.
     * @return Response
     */
    public function index()
    {
        $shelves = $this->entityRepo->getAllPaginated('bookshelf', 18);
        $recents = $this->signedIn ? $this->entityRepo->getRecentlyViewed('bookshelf', 4, 0) : false;
        $popular = $this->entityRepo->getPopular('bookshelf', 4, 0);
        $new = $this->entityRepo->getRecentlyCreated('bookshelf', 4, 0);
        $shelvesViewType = setting()->getUser($this->currentUser, 'bookshelves_view_type', config('app.views.bookshelves', 'grid'));
        $this->setPageTitle(trans('entities.shelves'));
        return view('shelves/index', [
            'shelves' => $shelves,
            'recents' => $recents,
            'popular' => $popular,
            'new' => $new,
            'shelvesViewType' => $shelvesViewType
        ]);
    }

    /**
     * Show the form for creating a new bookshelf.
     * @return Response
     */
    public function create()
    {
        $this->checkPermission('bookshelf-create-all');
        $this->setPageTitle(trans('entities.shelves_create'));
        $books = $this->entityRepo->getAll('book', false, 'update');
        return view('shelves/create', ['books' => $books]);
    }

    /**
     * Store a newly created book in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $this->checkPermission('bookshelf-create-all');
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'description' => 'string|max:1000',
        ]);

        $bookshelf = $this->entityRepo->createFromInput('bookshelf', $request->all());
        $this->entityRepo->updateShelfBooks($bookshelf, $request->get('books', ''));
        Activity::add($bookshelf, 'bookshelf_create');

        return redirect($bookshelf->getUrl());
    }

//
//    /**
//     * Display the specified book.
//     * @param $slug
//     * @return Response
//     */
//    public function show($slug)
//    {
//        $book = $this->entityRepo->getBySlug('book', $slug);
//        $this->checkOwnablePermission('book-view', $book);
//        $bookChildren = $this->entityRepo->getBookChildren($book);
//        Views::add($book);
//        $this->setPageTitle($book->getShortName());
//        return view('books/show', [
//            'book' => $book,
//            'current' => $book,
//            'bookChildren' => $bookChildren,
//            'activity' => Activity::entityActivity($book, 20, 0)
//        ]);
//    }
//
//    /**
//     * Show the form for editing the specified book.
//     * @param $slug
//     * @return Response
//     */
//    public function edit($slug)
//    {
//        $book = $this->entityRepo->getBySlug('book', $slug);
//        $this->checkOwnablePermission('book-update', $book);
//        $this->setPageTitle(trans('entities.books_edit_named', ['bookName'=>$book->getShortName()]));
//        return view('books/edit', ['book' => $book, 'current' => $book]);
//    }
//
//    /**
//     * Update the specified book in storage.
//     * @param  Request $request
//     * @param          $slug
//     * @return Response
//     */
//    public function update(Request $request, $slug)
//    {
//        $book = $this->entityRepo->getBySlug('book', $slug);
//        $this->checkOwnablePermission('book-update', $book);
//        $this->validate($request, [
//            'name' => 'required|string|max:255',
//            'description' => 'string|max:1000'
//        ]);
//         $book = $this->entityRepo->updateFromInput('book', $book, $request->all());
//         Activity::add($book, 'book_update', $book->id);
//         return redirect($book->getUrl());
//    }
//
//    /**
//     * Shows the page to confirm deletion
//     * @param $bookSlug
//     * @return \Illuminate\View\View
//     */
//    public function showDelete($bookSlug)
//    {
//        $book = $this->entityRepo->getBySlug('book', $bookSlug);
//        $this->checkOwnablePermission('book-delete', $book);
//        $this->setPageTitle(trans('entities.books_delete_named', ['bookName'=>$book->getShortName()]));
//        return view('books/delete', ['book' => $book, 'current' => $book]);
//    }
//
//    /**
//     * Shows the view which allows pages to be re-ordered and sorted.
//     * @param string $bookSlug
//     * @return \Illuminate\View\View
//     */
//    public function sort($bookSlug)
//    {
//        $book = $this->entityRepo->getBySlug('book', $bookSlug);
//        $this->checkOwnablePermission('book-update', $book);
//        $bookChildren = $this->entityRepo->getBookChildren($book, true);
//        $books = $this->entityRepo->getAll('book', false, 'update');
//        $this->setPageTitle(trans('entities.books_sort_named', ['bookName'=>$book->getShortName()]));
//        return view('books/sort', ['book' => $book, 'current' => $book, 'books' => $books, 'bookChildren' => $bookChildren]);
//    }
//
//    /**
//     * Shows the sort box for a single book.
//     * Used via AJAX when loading in extra books to a sort.
//     * @param $bookSlug
//     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
//     */
//    public function getSortItem($bookSlug)
//    {
//        $book = $this->entityRepo->getBySlug('book', $bookSlug);
//        $bookChildren = $this->entityRepo->getBookChildren($book);
//        return view('books/sort-box', ['book' => $book, 'bookChildren' => $bookChildren]);
//    }
//
//    /**
//     * Saves an array of sort mapping to pages and chapters.
//     * @param  string $bookSlug
//     * @param Request $request
//     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
//     */
//    public function saveSort($bookSlug, Request $request)
//    {
//        $book = $this->entityRepo->getBySlug('book', $bookSlug);
//        $this->checkOwnablePermission('book-update', $book);
//
//        // Return if no map sent
//        if (!$request->filled('sort-tree')) {
//            return redirect($book->getUrl());
//        }
//
//        // Sort pages and chapters
//        $sortMap = collect(json_decode($request->get('sort-tree')));
//        $bookIdsInvolved = collect([$book->id]);
//
//        // Load models into map
//        $sortMap->each(function ($mapItem) use ($bookIdsInvolved) {
//            $mapItem->type = ($mapItem->type === 'page' ? 'page' : 'chapter');
//            $mapItem->model = $this->entityRepo->getById($mapItem->type, $mapItem->id);
//            // Store source and target books
//            $bookIdsInvolved->push(intval($mapItem->model->book_id));
//            $bookIdsInvolved->push(intval($mapItem->book));
//        });
//
//        // Get the books involved in the sort
//        $bookIdsInvolved = $bookIdsInvolved->unique()->toArray();
//        $booksInvolved = $this->entityRepo->book->newQuery()->whereIn('id', $bookIdsInvolved)->get();
//        // Throw permission error if invalid ids or inaccessible books given.
//        if (count($bookIdsInvolved) !== count($booksInvolved)) {
//            $this->showPermissionError();
//        }
//        // Check permissions of involved books
//        $booksInvolved->each(function (Book $book) {
//             $this->checkOwnablePermission('book-update', $book);
//        });
//
//        // Perform the sort
//        $sortMap->each(function ($mapItem) {
//            $model = $mapItem->model;
//
//            $priorityChanged = intval($model->priority) !== intval($mapItem->sort);
//            $bookChanged = intval($model->book_id) !== intval($mapItem->book);
//            $chapterChanged = ($mapItem->type === 'page') && intval($model->chapter_id) !== $mapItem->parentChapter;
//
//            if ($bookChanged) {
//                $this->entityRepo->changeBook($mapItem->type, $mapItem->book, $model);
//            }
//            if ($chapterChanged) {
//                $model->chapter_id = intval($mapItem->parentChapter);
//                $model->save();
//            }
//            if ($priorityChanged) {
//                $model->priority = intval($mapItem->sort);
//                $model->save();
//            }
//        });
//
//        // Rebuild permissions and add activity for involved books.
//        $booksInvolved->each(function (Book $book) {
//            $this->entityRepo->buildJointPermissionsForBook($book);
//            Activity::add($book, 'book_sort', $book->id);
//        });
//
//        return redirect($book->getUrl());
//    }
//
//    /**
//     * Remove the specified book from storage.
//     * @param $bookSlug
//     * @return Response
//     */
//    public function destroy($bookSlug)
//    {
//        $book = $this->entityRepo->getBySlug('book', $bookSlug);
//        $this->checkOwnablePermission('book-delete', $book);
//        Activity::addMessage('book_delete', 0, $book->name);
//        $this->entityRepo->destroyBook($book);
//        return redirect('/books');
//    }
//
//    /**
//     * Show the Restrictions view.
//     * @param $bookSlug
//     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
//     */
//    public function showRestrict($bookSlug)
//    {
//        $book = $this->entityRepo->getBySlug('book', $bookSlug);
//        $this->checkOwnablePermission('restrictions-manage', $book);
//        $roles = $this->userRepo->getRestrictableRoles();
//        return view('books/restrictions', [
//            'book' => $book,
//            'roles' => $roles
//        ]);
//    }
//
//    /**
//     * Set the restrictions for this book.
//     * @param $bookSlug
//     * @param $bookSlug
//     * @param Request $request
//     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
//     */
//    public function restrict($bookSlug, Request $request)
//    {
//        $book = $this->entityRepo->getBySlug('book', $bookSlug);
//        $this->checkOwnablePermission('restrictions-manage', $book);
//        $this->entityRepo->updateEntityPermissionsFromRequest($request, $book);
//        session()->flash('success', trans('entities.books_permissions_updated'));
//        return redirect($book->getUrl());
//    }
//
//    /**
//     * Export a book as a PDF file.
//     * @param string $bookSlug
//     * @return mixed
//     */
//    public function exportPdf($bookSlug)
//    {
//        $book = $this->entityRepo->getBySlug('book', $bookSlug);
//        $pdfContent = $this->exportService->bookToPdf($book);
//        return response()->make($pdfContent, 200, [
//            'Content-Type'        => 'application/octet-stream',
//            'Content-Disposition' => 'attachment; filename="' . $bookSlug . '.pdf'
//        ]);
//    }
//
//    /**
//     * Export a book as a contained HTML file.
//     * @param string $bookSlug
//     * @return mixed
//     */
//    public function exportHtml($bookSlug)
//    {
//        $book = $this->entityRepo->getBySlug('book', $bookSlug);
//        $htmlContent = $this->exportService->bookToContainedHtml($book);
//        return response()->make($htmlContent, 200, [
//            'Content-Type'        => 'application/octet-stream',
//            'Content-Disposition' => 'attachment; filename="' . $bookSlug . '.html'
//        ]);
//    }
//
//    /**
//     * Export a book as a plain text file.
//     * @param $bookSlug
//     * @return mixed
//     */
//    public function exportPlainText($bookSlug)
//    {
//        $book = $this->entityRepo->getBySlug('book', $bookSlug);
//        $htmlContent = $this->exportService->bookToPlainText($book);
//        return response()->make($htmlContent, 200, [
//            'Content-Type'        => 'application/octet-stream',
//            'Content-Disposition' => 'attachment; filename="' . $bookSlug . '.txt'
//        ]);
//    }
}