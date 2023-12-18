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
        $user = Auth::user();
        $allFranchiseCategory = FranchiseCategory::orderBy('franchiseCategory','asc')->get();
        return view("franchisor.add_franchise", compact('user','allFranchiseCategory'));
    }

    public function StoreFranchise(Request $req){
        // dd($req->all());

        // Validate the form data
        $validatedData = $req->validate([
            'franchiseName' => 'required|string|max:255',
            'franchiseLocation' => 'required|string|max:255',
            'franchiseCategory' => 'required|string|max:20',
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
            'franchisePrice' => $validatedData['franchisePrice'], 
            'franchiseReport' => $saveReportUrl,
            'franchisePIC' => $userId,
            'franchisePICName' => $username,
            'franchiseLogo' => $saveLogoUrl,
            'franchise_category_id' => $validatedData['franchiseCategory'],
            'status' => 'Requested',
            'created_at' => Carbon::now(),
        ]);
        
        // $notification = array(
        //     'message' => 'Franchise Submitted! Please wait for approval',
        //     'alert-type' => 'success',
        // ); 

        // return redirect()->route('dashboard')->with($notification);


        $response = [
            'message' => 'Franchise registered successfully, please wait for approval',
            'modal' => '#successModal', // Modal ID to trigger
            ];

        // Flash the data to the session
        session()->flash('success_data', $response);        
    
        return  redirect()->route('dashboard')->with('successData', $response);

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

        $allFranchise = Franchise::where('franchisePIC',$userId)->orderBy('created_at','desc')->get();
        $franchiseCategories = FranchiseCategory::all();
        $myFranchise = true;

        return view('franchise.franchise', compact('allFranchise','franchiseCategories', 'myFranchise'));
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
   

    public function Franchise(){
        $allFranchise = Franchise::where('status','approved')->get();
        $franchiseCategories = FranchiseCategory::all();
        $myFranchise = false;

        return view('franchise.franchise', compact('allFranchise','franchiseCategories', 'myFranchise'));
    }

    public function FranchiseByCategory($categoryId){
        $franchise = Franchise::where('franchiseCategoryId', $categoryId)->latest()->limit(4)->get();
        $categories = FranchiseCategory::all();
        $latestFranchise = Franchise::latest()->limit(4)->get();
        $myFranchise = false;

        return view('franchise.franchise', compact('categories','latestFranchise','franchise', 'myFranchise'));
    }

    public function detail($id)
    {
        // GET USER
        $user = Auth::user();

        // GET FRANCHISE
        $franchise = Franchise::findOrFail($id);
        $franchisor = User::where('id', $franchise->franchisePIC)->first();
        $allFranchiseCategory = FranchiseCategory::orderBy('franchiseCategory','asc')->get();
        $otherFranchise = Franchise::where('franchise_category_id', $franchise->franchise_category_id)->whereNot('id', $id)->limit(3)->get();

        // GET RATINGS 
        $ratings = FranchiseRating::where(['franchiseId' => $id, 'rating' => 5])->limit(5)->get();

        if($franchise->status != 'Approved' && $user->id != $franchise->franchisePIC)
        {
            abort(404);
        }
        
        else
        {
            return view('franchise.franchiseDetail', compact('franchise', 'otherFranchise', 'ratings','franchisor', 'allFranchiseCategory'));
        }
    }

    public function sendProposal(Request $request, $franchiseId)
    {
        // get user
        $user = Auth::user(); 

        if(!Auth::check())
        {
            $message = "Login to send a proposal!";
            return redirect()->back()->with('error', $message);
        }

        else
        {
            // validation for when user has sent a proposal before
            $franchiseProposalBasedOnUser = FranchiseProposal::where(['user_id' => $user->id, 'franchise_id' => $franchiseId]);
            if($franchiseProposalBasedOnUser->count() > 0)
            {
                $message = 'You have already sent a proposal to this franchise!';
                return redirect()->back()->with('error', $message);
            }
            
            else
            {
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
                
                if (!File::isDirectory($directory)) 
                {
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
                
                $message = 'Proposal sent successfully';
                return redirect()->back()->with('success', $message);
            }
        }
    }

    public function deleteFile($directory)
    {
        if (file_exists(public_path($directory))) 
        {
             unlink(public_path($directory));
        }
    }

    public function editFranchise(Request $req, $franchiseId)
    {
        // Find franchise
        $franchise = Franchise::find($franchiseId);

        // Validate data
        $validatedData = $req->validate([
            'franchiseName' => 'required|string|max:255',
            'franchiseLocation' => 'required|string|max:255',
            'franchiseCategory' => 'required|string|max:20',
            'franchisePrice' => 'required|integer',
            'franchiseReport' => 'file|mimes:pdf,doc,docx,xls,xlsx,zip',
            'franchiseLogo' => 'image|mimes:jpeg,jpg,png',
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
        if($franchiseReport == null && $franchiseLogo == null)
        {
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
        else if($franchiseReport == null)
        {
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
        else if($franchiseLogo == null)
        {
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
        else
        {
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
}
