<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Models\Franchise;
use App\Models\FranchiseCategory;
use App\Models\FranchiseRating;
use App\Models\FranchiseProposal;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use App\Models\User;
use Mail;
use App\Mail\SendMail;


class FranchiseController extends Controller
{
    public function AllFranchise(){
       $allFranchise = Franchise::latest()->get();

        return view("admin.franchise.all_franchise", compact('allFranchise'));
    } // end method

    public function AllFranchiseRequest(){
        $allFranchise = Franchise::latest()->where('status','Requested')->get();

        return view("admin.franchise.all_franchise_request", compact('allFranchise'));
    }


    public function RegisterFranchise(){
         if (!Auth::check()) {
                $message = "Login to view history!";
                return redirect()->back()->with('error', $message);
        } else {
            $user = Auth::user();
            $allFranchiseCategory = FranchiseCategory::orderBy('franchiseCategory','asc')->get();
            return view("franchisor.add_franchise", compact('user','allFranchiseCategory'));
        }
    }

    public function StoreFranchise(Request $req){
         if (!Auth::check()) {
                $message = "Login to add franchise!";
                return redirect()->back()->with('error', $message);
        } else {
            // Validate the form data
            $validatedData = $req->validate([
                'franchiseName' => 'required|string|max:255',
                'franchiseLocation' => 'required|string|max:255',
                'franchiseCategory' => 'required|string|max:20',
                'franchiseDesc' => 'required|string',
                'franchisePrice' => 'required|integer',
                'franchiseReport' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,zip',
                'franchiseLogo' => 'required|image|mimes:jpeg,jpg,png',
            ], [
                'franchiseName.required' => 'Franchise name is required.',
                'franchiseName.string' => 'Franchise name must be a string.',
                'franchiseName.max' => 'Franchise name should not exceed 255 characters.',
                
                'franchiseLocation.required' => 'Franchise location is required.',
                'franchiseLocation.string' => 'Franchise location must be a string.',
                'franchiseLocation.max' => 'Franchise location should not exceed 255 characters.',
                
                'franchiseCategory.required' => 'Franchise category is required.',
                
                'franchiseDesc.required' => 'Franchise description is required',

                'franchisePrice.required' => 'Franchise price is required.',
                'franchisePrice.integer' => 'Franchise price must be an integer.',
                
                'franchiseReport.required' => 'Franchise report is required.',
                'franchiseReport.file' => 'Franchise report must be a file.',
                'franchiseReport.mimes' => 'Franchise report must be in PDF, Word, Excel, or ZIP format.',
                
                'franchiseLogo.required' => 'Franchise logo is required.',
                'franchiseLogo.image' => 'Franchise logo must be an image.',
                'franchiseLogo.mimes' => 'Franchise logo must be in JPEG, JPG, or PNG format.',
        
            ]);

            // dd($validatedData);

            $franchiseReport = $req->file('franchiseReport');
            $name_gen_report = hexdec(uniqid()). '.' . $franchiseReport->getClientOriginalExtension();
            $directoryReport = 'upload/FranchiseReport/';
            $saveReportUrl = $directoryReport . $name_gen_report; 

            $franchiseLogo = $req->file('franchiseLogo');
            $name_gen_logo = hexdec(uniqid()). '.' . $franchiseLogo->getClientOriginalExtension();
            $directory = 'upload/FranchiseLogo/';
            $saveLogoUrl = $directory . $name_gen_logo; 


            //get user
            $userId = Auth::id();
            $username = Auth::user()->name;

            // Create the directory if it doesn't exist
            if (!File::isDirectory($directoryReport)) {
                File::makeDirectory($directoryReport);
            }

            // Create the directory if it doesn't exist
            if (!File::isDirectory($directory)) {
                File::makeDirectory($directory);
            }

            //save to directory
            $franchiseReport->move($directoryReport, $name_gen_report);

            //store image
            Image::make($franchiseLogo)->resize(800,450)->save(public_path($directory . $name_gen_logo));

            //get franchise category name
            $franchiseCategory = FranchiseCategory::findOrFail($validatedData['franchiseCategory'])->franchiseCategory;

            Franchise::insert([
                'franchiseName' => $validatedData['franchiseName'],
                'franchiseLocation' => $validatedData['franchiseLocation'],
                'franchiseCategory' => $franchiseCategory,
                'franchiseDesc' => $validatedData['franchiseDesc'],
                'franchisePrice' => $validatedData['franchisePrice'], 
                'franchiseReport' => $saveReportUrl,
                'franchiseDesc' => $req->franchiseDesc,
                'franchisePIC' => $userId,
                'franchisePICName' => $username,
                'franchiseLogo' => $saveLogoUrl,
                'franchise_category_id' => $validatedData['franchiseCategory'],
                'status' => 'Requested',
                'created_at' => Carbon::now(),
            ]);
            
            return redirect()->back()->with('success', 'Franchise registered successfully, please wait for your approval!');
        }
    }

    public function ApproveFranchise($id){
        $franchise = Franchise::findOrFail($id);

        $franchise->status = 'Approved';
        $franchise->save();

        $notification = array(
            'message' => $franchise->franchiseName.' Approved!',
            'alert-type' => 'success',
        );
        return redirect()->back()->with($notification);
    }

    public function MyFranchise(){
        //get user
        $userId = Auth::id();

        $allFranchise = Franchise::where('franchisePIC', $userId)
                        ->orWhere('boughtBy', $userId)
                        ->orderBy('created_at','desc')->get();
        $franchiseCategories = FranchiseCategory::all();

        return view('franchisor.my_franchise', compact('allFranchise','franchiseCategories'));
    }
    

    public function RejectFranchise($id){
        $franchise = Franchise::findOrFail($id);

        $franchise->status = 'Rejected';
        $franchise->save();

        $notification = array(
            'message' => $franchise->franchiseName.' Rejected!',
            'alert-type' => 'success',
        );
        return redirect()->back()->with($notification);
    }
   

    public function Franchise(Request $request){
        // GET PARAMETER VALUES
        $categoryId = $request->input('category');
        $minPrice = $request->input('minPrice');
        $maxPrice = $request->input('maxPrice');
        $rating = $request->input('rating');

        $queryAllFranchise = Franchise::query()->where('status','approved');

        if ($categoryId !== null) {
            $queryAllFranchise->where('franchiseCategory', $categoryId);
        }

        if ($minPrice !== null) {
            $queryAllFranchise->where('franchisePrice', '>=', $minPrice);
        }

        if ($maxPrice !== null) {
            $queryAllFranchise->where('franchisePrice', '<=', $maxPrice);
        }

        if ($rating !== null) {
            $queryAllFranchise->where('franchiseRating', $rating);
        }

        $allFranchise = $queryAllFranchise->paginate(12);
        $franchiseCategories = FranchiseCategory::all();

        return view('franchise.franchise', compact('allFranchise','franchiseCategories'));
    }

    public function browseAllFranchise(Request $request) {
        // GET PARAMETER VALUES
        $categoryId = $request->input('category');
        $minPrice = $request->input('minPrice');
        $maxPrice = $request->input('maxPrice');
        $rating = $request->input('rating');
        $isBought = $request->input('isPurchased');

        $queryAllFranchise = Franchise::query()->where('status','approved');

        if ($categoryId !== null) {
            $queryAllFranchise->where('franchise_category_id', $categoryId);
        }

        if ($minPrice !== null) {
            $queryAllFranchise->where('franchisePrice', '>=', $minPrice);
        }

        if ($maxPrice !== null) {
            $queryAllFranchise->where('franchisePrice', '<=', $maxPrice);
        }

        if ($rating !== null) {
            $queryAllFranchise->where('franchiseRating', $rating);
        }

        if ($isBought !== null) {
            $queryAllFranchise->where('isBought', $isBought);
        }

        $allFranchise = $queryAllFranchise->paginate(12);
        $franchiseCategories = FranchiseCategory::all();

        return view('franchise.allFranchise', compact('allFranchise','franchiseCategories'));
    }

    public function FranchiseByCategory($categoryId) {
        $franchise = Franchise::where('franchiseCategoryId', $categoryId)->latest()->limit(4)->get();
        $categories = FranchiseCategory::all();
        $latestFranchise = Franchise::latest()->limit(4)->get();

        return view('franchise.franchise', compact('categories','latestFranchise','franchise'));
    }

    public function detail($id) {
        // GET USER
        $user = Auth::user();

        // GET FRANCHISE
        $franchise = Franchise::findOrFail($id);
        $franchisor = User::where('id', $franchise->franchisePIC)->first();
        $allFranchiseCategory = FranchiseCategory::orderBy('franchiseCategory','asc')->get();
        $otherFranchise = Franchise::where('franchise_category_id', $franchise->franchise_category_id)->where('status', 'Approved')->whereNot('id', $id)->limit(4)->get();

        // GET RATINGS 
        $ratings = FranchiseRating::where(['franchiseId' => $id, 'rating' => 5])->limit(5)->get();

        if($franchise->status != 'Approved' && $user->id != $franchise->franchisePIC) {
            abort(404);
        }
        
        else {
            return view('franchise.franchiseDetail', compact('franchise', 'otherFranchise', 'ratings','franchisor', 'allFranchiseCategory'));
        }
    }

    public function sendProposal(Request $request, $franchiseId) {
        // get user
        $user = Auth::user(); 

        if(!Auth::check()) {
            $message = "Login to send a proposal!";
            return redirect()->back()->with('error', $message);
        }

        else {
            // validation for when user has sent a proposal before
            $franchiseProposalBasedOnUser = FranchiseProposal::where([
                ['user_id', '=', $user->id],
                ['franchise_id', '=', $franchiseId],
                ['status', '<>', 'Rejected'],
            ]);

            if($franchiseProposalBasedOnUser->count() > 0) {
                if($franchiseProposalBasedOnUser->status === 'Approved') {
                    $message = 'Your proposal has already been approved by the franchisor!';
                    return redirect()->back()->with('error', $message);

                } else {
                    $message = 'You have already sent a proposal to this franchise!';
                    return redirect()->back()->with('error', $message);
                }
            }
            
            else {
                $customMessages = [
                    'proposalFile.required' => 'Proposal is Required!',
                    'proposalFile.mimes' => 'Proposal must be in one of the allowed file formats above.',
                    'proposalDescription.max' => 'Proposal description must not exceed 255 characters!'
                ];
                
                $validatedData = $request->validate([
                    'proposalFile' => 'required|mimes:pdf,doc,docx,xls,xlsx',
                    'proposalDescription' => 'string|max:255'
                ], $customMessages);
        
                $proposal = $request->file('proposalFile');
                $name_gen_proposal = hexdec(uniqid()). '.' . $proposal->getClientOriginalExtension();
        
                $directory = 'upload/franchiseProposal/';
                $save_url_proposal = $directory . $name_gen_proposal;
                
                if (!File::isDirectory($directory)) {
                    File::makeDirectory($directory);
                }
        
                // store proposal
                $proposal->move(public_path($directory), $name_gen_proposal);
        
                FranchiseProposal::insert([
                    'proposerName' => $user->name,
                    'proposerEmail' => $user->email,
                    'proposerPhoneNumber' => $user->phoneNumber,
                    'proposalFile' => $save_url_proposal, 
                    'proposalDescription' => $validatedData['proposalDescription'],
                    'franchise_id' => $franchiseId,
                    'user_id' => $user->id,
                    'status' => 'Requested',
                    'created_at' => Carbon::now(),
                ]);
                
                $franchise = Franchise::find($franchiseId);
                $franchisor = $franchise->franchisor;

                // send message to franchisor
                $data = [
                    'recipient' => $franchisor->email,
                    'fromName' => $franchisor->name,
                    'userEmail' => $user->email,
                    'link' => route('proposal.franchise'),
                ];

                // Mail::send('emails.user-notification-via-email', ['data' => $data], function($message) use ($data, $franchise) {
                //     $message->to($data['recipient'])
                //         ->from('adm.franchiseku@gmail.com', 'FranchiseKu Admin')
                //         ->subject('You have a proposal request!');
                // });

                Mail::send('emails.notify-franchisor-via-email', ['data' => $data], function ($message) use ($data, $franchise) {
                    $message->to($data['recipient'])
                        ->from('adm.franchiseku@gmail.com', 'FranchiseKu Admin')
                        ->subject('You have a proposal request!');
                });

                // send message to proposer
                $data = [
                    'recipient' => $user->email,
                    'fromName' => $user->name,
                    'message' => 'We have received your proposal and have informed our fellow Franchisor from ' . $franchise->franchiseName .' . Any further updates will be emailed to you as soon as possible! Thank you for your patience.'
                ];

                Mail::send('emails.user-notification-via-email', ['data' => $data], function($message) use ($data, $franchise) {
                    $message->to($data['recipient'])
                        ->from('adm.franchiseku@gmail.com', 'FranchiseKu Admin')
                        ->subject('We have received your proposal for ' . $franchise->franchiseName . '!');
                });

                $message = 'Proposal sent successfully';
                return redirect()->back()->with('success', $message);
            }
        }
    }

    public function deleteFile($directory) {
        if (file_exists(public_path($directory))) {
             unlink(public_path($directory));
        }
    }

    public function editFranchise(Request $req, $franchiseId) {
        // Find franchise
        $franchise = Franchise::find($franchiseId);

        if($franchise->isBought == 1) {
            $message = "Unable to edit! Franchise has been bought!";
            return redirect(route('franchise.detail'))->with('success', $message);

        } else {
            // Validate data
            $validatedData = $req->validate([
                'franchiseName' => 'required|string|max:255',
                'franchiseLocation' => 'required|string|max:255',
                'franchiseCategory' => 'required|string|max:20',
                'franchisePrice' => 'required|integer',
                'franchiseReport' => 'file|mimes:pdf,doc,docx,xls,xlsx,zip',
                'franchiseLogo' => 'image|mimes:jpeg,jpg,png',
                'franchiseDesc' => 'required|string'
            ], 
            [
                'franchiseName.required' => 'Franchise name is required.',
                'franchiseName.string' => 'Franchise name must be a string.',
                'franchiseName.max' => 'Franchise name should not exceed 255 characters.',
                
                'franchiseLocation.required' => 'Franchise location is required.',
                'franchiseLocation.string' => 'Franchise location must be a string.',
                'franchiseLocation.max' => 'Franchise location should not exceed 255 characters.',
                
                'franchiseCategory.required' => 'Franchise category is required.',
                
                'franchisePrice.required' => 'Franchise price is required.',
                'franchisePrice.integer' => 'Franchise price must be an integer.',
    
                'franchiseReport.file' => 'Franchise report must be a file.',
                'franchiseReport.mimes' => 'Franchise report must be in PDF, Word, Excel, or ZIP format.',
    
                'franchiseLogo.image' => 'Franchise logo must be an image.',
                'franchiseLogo.mimes' => 'Franchise logo must be in JPEG, JPG, or PNG format.',
            
                'franchiseDesc.required' => 'Franchise Description '
            ]);
    
            //get franchise category name
            $franchiseCategory = FranchiseCategory::findOrFail($validatedData['franchiseCategory'])->franchiseCategory;
    
            // get franchise report & logo
            $franchiseReport = $req->file('franchiseReport');
            $franchiseLogo = $req->file('franchiseLogo');
            
            //get user
            $userId = Auth::id();
            $username = Auth::user()->name;
    
            // edit without franchise report & logo
            if($franchiseReport == null && $franchiseLogo == null) {
                // update database without franchise report & logo
                $franchise->update([
                    'franchiseName' => $validatedData['franchiseName'],
                    'franchiseLocation' => $validatedData['franchiseLocation'],
                    'franchiseCategory' => $franchiseCategory,
                    'franchisePrice' => $validatedData['franchisePrice'], 
                    'franchisePIC' => $userId,
                    'franchisePICName' => $username,
                    'franchise_category_id' => $validatedData['franchiseCategory'],
                    'status' => 'Requested',
                    'created_at' => Carbon::now(),
                ]);
            }
    
            // edit without franchise report
            else if($franchiseReport == null) {
                // delete old logo
                $currentLogoUrl = $franchise->franchiseLogo;
                $this->deleteFile($currentLogoUrl);
                
                // Update franchise logo
                $name_gen_logo = hexdec(uniqid()). '.' . $franchiseLogo->getClientOriginalExtension();
                $directoryLogo = 'upload/FranchiseLogo/';
                $saveLogoUrl = $directoryLogo . $name_gen_logo; 
    
                // create logo directory if it doesn't exist
                if (!File::isDirectory($directoryLogo)) {
                    File::makeDirectory($directoryLogo);
                }
                
                //save logo to directory
                Image::make($franchiseLogo)->resize(800,450)->save(public_path($directoryLogo . $name_gen_logo));
    
                // update database without franchise report
                $franchise->update([
                    'franchiseName' => $validatedData['franchiseName'],
                    'franchiseLocation' => $validatedData['franchiseLocation'],
                    'franchiseCategory' => $franchiseCategory,
                    'franchisePrice' => $validatedData['franchisePrice'], 
                    'franchisePIC' => $userId,
                    'franchisePICName' => $username,
                    'franchiseLogo' => $saveLogoUrl,
                    'franchise_category_id' => $validatedData['franchiseCategory'],
                    'status' => 'Requested',
                    'created_at' => Carbon::now(),
                ]);
            }
    
            // edit without franchise logo
            else if($franchiseLogo == null) {
                // delete old report
                $currentReportUrl = $franchise->franchiseReport;
                $this->deleteFile($currentReportUrl);
    
                // update franchise report
                $name_gen_report = hexdec(uniqid()). '.' . $franchiseReport->getClientOriginalExtension();
                $directoryReport = 'upload/FranchiseReport/';
                $saveReportUrl = $directoryReport . $name_gen_report; 
    
                // create report directory if it doesn't exist
                if (!File::isDirectory($directoryReport)) {
                    File::makeDirectory($directoryReport);
                }
    
                //save to directory
                $franchiseReport->move($directoryReport, $name_gen_report);
    
                $franchise->update([
                    'franchiseName' => $validatedData['franchiseName'],
                    'franchiseLocation' => $validatedData['franchiseLocation'],
                    'franchiseCategory' => $franchiseCategory,
                    'franchisePrice' => $validatedData['franchisePrice'], 
                    'franchisePIC' => $userId,
                    'franchisePICName' => $username,
                    'franchiseReport' => $saveReportUrl,
                    'franchise_category_id' => $validatedData['franchiseCategory'],
                    'status' => 'Requested',
                    'created_at' => Carbon::now(),
                ]);
            }
    
            // edit with report and logo
            else {
                // delete old logo
                $currentLogoUrl = $franchise->franchiseLogo;
                $this->deleteFile($currentLogoUrl);
                
                // Update franchise logo
                $name_gen_logo = hexdec(uniqid()). '.' . $franchiseLogo->getClientOriginalExtension();
                $directoryLogo = 'upload/FranchiseLogo/';
                $saveLogoUrl = $directoryLogo . $name_gen_logo; 
    
                // delete old report
                $currentReportUrl = $franchise->franchiseReport;
                $this->deleteFile($currentReportUrl);
    
                // update franchise report
                $name_gen_report = hexdec(uniqid()). '.' . $franchiseReport->getClientOriginalExtension();
                $directoryReport = 'upload/FranchiseReport/';
                $saveReportUrl = $directoryReport . $name_gen_report; 
    
                // Create the directory if it doesn't exist
                if (!File::isDirectory($directoryReport)) {
                    File::makeDirectory($directoryReport);
                }
        
                // Create the directory if it doesn't exist
                if (!File::isDirectory($directoryLogo)) {
                    File::makeDirectory($directoryLogo);
                }
        
                //save to directory
                $franchiseReport->move($directoryReport, $name_gen_report);
        
                //store image
                Image::make($franchiseLogo)->resize(800,450)->save(public_path($directoryLogo . $name_gen_logo));
        
                $franchise->update([
                    'franchiseName' => $validatedData['franchiseName'],
                    'franchiseLocation' => $validatedData['franchiseLocation'],
                    'franchiseCategory' => $franchiseCategory,
                    'franchisePrice' => $validatedData['franchisePrice'], 
                    'franchiseReport' => $saveReportUrl,
                    'franchisePIC' => $userId,
                    'franchisePICName' => $username,
                    'franchiseLogo' => $saveLogoUrl,
                    'franchise_category_id' => $validatedData['franchiseCategory'],
                    'status' => 'Requested',
                    'created_at' => Carbon::now(),
                ]);
            }
            
            $message = 'Franchise updated successfully!';
            return redirect()->back()->with('success', $message);
        }
    }

    public function deleteFranchise($id) {
        $franchise = Franchise::findOrFail($id);
        $franchiseProposals = FranchiseProposal::where('franchise_id', $id)->get();

        if($franchise->isBought == 1) {
            $message = 'Unable to delete franchise! Franchise has been bought.';
            return redirect(route('my.franchise'))->with('error', $message);
        } else {
            // send email notification to proposer 
            foreach($franchiseProposals as $franchiseProposal) {
                $data = [
                    'recipient' => $franchiseProposal->proposerEmail,
                    'fromName' => $franchiseProposal->proposerName,
                    'message' => 'Unfortunatey, our fellow franchisor from ' . $franchiseProposal->franchise->franchiseName . ' has deleted their franchise. We have automatically deleted your proposal, our deepest condolences.'
                ];
        
                Mail::send('emails.user-notification-via-email', ['data' => $data, 'franchiseProposal' => $franchiseProposal], function($message) use ($data, $franchiseProposal) {
                    $message->to($data['recipient'])
                        ->from('adm.franchiseku@gmail.com', 'FranchiseKu Admin')
                        ->subject('Your proposal to ' . $franchiseProposal->franchise->franchiseName . ' has been deleted!');
                });
                
                $franchiseProposal->delete();
            }
    
            $franchise->delete();
    
            $message = "Franchise has been deleted!";
            return redirect('my.franchise')->with($message);
        }
    }

    public function historyFranchise(Request $request){
        //get user
        $user = Auth::user();

        // get parameter values
        $status = $request->input('status');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        // filter data
        $queryFranchiseProposal = FranchiseProposal::query()->where('user_id',$user->id)->orderBy('created_at','desc');

        if ($status !== null) 
        {
            $queryFranchiseProposal->where('status', $status);
        }

        if ($startDate !== null) 
        {
            $queryFranchiseProposal->where('created_at', '>=', $startDate);
        }

        if ($endDate !== null) 
        {
            $queryFranchiseProposal->where('created_at', '<=', $endDate);
        }

        // fetch filtered data
        $franchiseProposals = $queryFranchiseProposal->paginate(4);

        return view('franchise.historyFranchise', compact('franchiseProposals'));
    }

    public function searchHistory(Request $request)
    {
        $franchiseProposals = FranchiseProposal::whereHas(
            'franchise', function ($query) use ($request) {
                $query->where('franchiseName', 'like', '%' . $request->searchValue . '%');
            }
        )->paginate(4);

        return view(
            'franchise.historyFranchise',
            compact('franchiseProposals')
        );
    }

    public function franchiseProposalRequest(Request $request)
    {
        $franchisorId = Auth::user()->id; 
        $ownedFranchises = Franchise::where(['franchisePIC' => $franchisorId])->get();
        $ownedFranchiseIds = $ownedFranchises->pluck('id')->toArray();
        $queryFranchiseProposals = FranchiseProposal::query()->whereIn('franchise_id', $ownedFranchiseIds)->where('status', 'Requested')->orderBy('created_at','desc');

        $franchise = $request->input('franchise');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        if($franchise !== null)
        {
            $queryFranchiseProposals->where('franchise_id', $request->input('franchise'));
        }

        if ($startDate !== null) 
        {
            $queryFranchiseProposals->where('created_at', '>=', $startDate);
        }

        if ($endDate !== null) 
        {
            $queryFranchiseProposals->where('created_at', '<=', $endDate);
        }

        $franchiseProposals = $queryFranchiseProposals->paginate(10);

        return view('franchise.franchiseProposalRequests', compact('franchiseProposals', 'ownedFranchises'));
    }

    public function approveFranchiseProposal($id){
        $user = Auth::user();
        $franchiseProposal = FranchiseProposal::findOrFail($id);

        $franchiseProposal->status = 'Approved';
        $franchiseProposal->franchise->isBought = 1;
        $franchiseProposal->franchise->boughtBy = $franchiseProposal->user->id;
        $franchiseProposal->franchise->save();
        $franchiseProposal->save();

        // send message to user
        $data = [
            'recipient' => $franchiseProposal->proposerEmail,
            'fromName' => $franchiseProposal->proposerName,
            'message' => 'Our fellow franchisor from ' . $franchiseProposal->franchise->franchiseName . ' has accepted your proposal! We welcome you as one of our investors! Thank you for your collaboration.'
        ];

        Mail::send('emails.user-notification-via-email', ['data' => $data, 'franchiseProposal' => $franchiseProposal], function($message) use ($data, $franchiseProposal) {
            $message->to($data['recipient'])
                ->from('adm.franchiseku@gmail.com', 'FranchiseKu Admin')
                ->subject('Your proposal to ' . $franchiseProposal->franchise->franchiseName . ' has been accepted!');
        });

        $otherFranchiseProposal = FranchiseProposal::whereNot('id', $id)->get();
        foreach($otherFranchiseProposal as $item){
            $item->status = 'Rejected';
            $item->save();

            $otherData = [
                'recipient' => $item->proposerEmail,
                'fromName' => $item->proposerName,
                'message' => 'Unfortunately, our fellow franchisor from ' . $item->franchise->franchiseName . ' has rejected your proposal! We are very sorry to hear this. Thank you for your patience.'
            ];

            Mail::send('emails.user-notification-via-email', ['data' => $otherData, 'item' => $item], function($message) use ($otherData, $item) {
                $message->to($otherData['recipient'])
                    ->from('adm.franchiseku@gmail.com', 'FranchiseKu Admin')
                    ->subject('Your proposal to ' . $item->franchise->franchiseName . ' has been rejected!');
            });
        }

        $message = 'You have successfully approved the proposal!';
        return redirect()->back()->with('success', $message);
    }

    public function rejectFranchiseProposal($id){
        $user = Auth::user();
        $franchiseProposal = FranchiseProposal::findOrFail($id);

        $franchiseProposal->status = 'Rejected';
        $franchiseProposal->save();

        // send message to user
        $data = [
            'recipient' => $franchiseProposal->proposerEmail,
            'fromName' => $franchiseProposal->proposerName,
            'message' => 'Unfortunately, our fellow franchisor from ' . $franchiseProposal->franchise->franchiseName . ' has rejected your proposal! We are very sorry to hear this. Thank you for your patience.'
        ];

        Mail::send('emails.user-notification-via-email', ['data' => $data, 'franchiseProposal' => $franchiseProposal], function($message) use ($data, $franchiseProposal) {
            $message->to($data['recipient'])
                ->from('adm.franchiseku@gmail.com', 'FranchiseKu Admin')
                ->subject('Your proposal to ' . $franchiseProposal->franchise->franchiseName . ' has been rejected!');
        });

        $message = 'You have successfully rejected the proposal!';
        return redirect()->back()->with('success', $message);
    }

    public function rateFranchise(Request $request, $franchiseId)
    {
        if ($request->isMethod('POST')) {
            if (!Auth::check()) {
                $message = "Login to rate this franchise!";
                return redirect()->back()->with('error', $message);
            } else {
                // VALIDATE FOR WHEN USER ALREADY RATED CONTENT
                $ratingCount = FranchiseRating::where([
                    'userId' => Auth::user()->id,
                    'franchiseId' => $franchiseId,
                ])->count();
                if ($ratingCount > 0) {
                    $message = "You have already rated this franchise!";
                    return redirect()->back()->with('error', $message);
                } else {
                    // DO SOME VALIDATION HERE YEA
                    if ($request->rating == null || $request->rating == "") {
                        $message = "You haven't given this franchise a rating!";
                        return redirect()->back()->with('error', $message);
                    } elseif (
                        $request->ratingComment == null ||
                        $request->ratingComment == ""
                    ) {
                        $message = "Give the franchise a comment first!";
                        return redirect()->back()->with('error', $message);
                    } else {
                        // STORE THE RATING
                        $rating = new FranchiseRating();
                        $rating->userId = Auth::user()->id;
                        $rating->franchiseId = $franchiseId;
                        $rating->rating = $request->rating;
                        $rating->comment = $request->ratingComment;
                        $rating->save();

                        // CALCULATE AVERAGE RATING AND STORE IT
                        $averageRating = FranchiseRating::calculateAverageRating(
                            $franchiseId
                        );

                        $franchise = Franchise::findOrFail(
                            $franchiseId
                        );
                        $franchise->franchiseRating = $averageRating;
                        $franchise->save();

                        $message = 'Rating submitted successfully.';
                        return redirect()->back()->with('success', $message);
                    }
                }
            }
        }
    }
}
