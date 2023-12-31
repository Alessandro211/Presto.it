<?php

namespace App\Http\Livewire;

use App\Models\Article;
use Livewire\Component;
use App\Models\Category;
use App\Jobs\RemoveFaces;
use App\Jobs\ResizeImage;
use Livewire\WithFileUploads;
use App\Jobs\GoogleVisionLabelImage;
use App\Jobs\GoogleVisionSafeSearch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class ArticleForm extends Component
{

    use WithFileUploads;
 
    public $temporary_images;
    public $images = [];
    public $image;
    public $title;
    public $body;
    public $price;
    public $category;
    public $user_id;
    public $article;



    protected $rules = [
        'title' => 'required|min:6',
        'body' =>'required|min:15',
        'price' => 'required',
        'category' =>'required|different:$category',
        'images.*'=>'image|max:1024',
        'temporary_images.*'=>'image|max:1024',
    ];
    protected $messages = [

        'title.required' => 'Il titolo è obbligatorio',
        'title.min' => 'Il titolo deve avere almeno 6 caratteri',
        'body.required' => 'La descrizione è obbligatoria',
        'body.min' => 'La descrizione deve avere almeno 15 caratteri',
        'price.required' => 'Il prezzo è obbligatorio',
        'category.required' => 'La categoria è obbligatoria',
        'temporary_images.required'=>'L\'immagine è obbligatoria',
        'temporary_images.*.image'=>'I file devono essere immagini',
        'temporary_images.*.max'=>'L\'immagine deve essere massimo di 1Mb',
        'images.image'=>'L\'immagine deve essere un\'immgine',
        'images.max'=>'L\'immagine deve essere massimo di 1Mb',

    ];

    public function updatedTemporaryImages(){
        if ($this->validate([
            'temporary_images.*'=>'image|max:1024',
        ])) {
            foreach ($this->temporary_images as $image) {
                $this->images[] = $image;
            }
        }
    }

    public function removeImage($key){
        if (in_array($key, array_keys($this->images))) {
            unset($this->images[$key]);
        }
    }

    public function cleanForm(){
         $this->temporary_images;
         $this->images = [];
         $this->image;
         $this->title;
         $this->body;
         $this->price;
         $this->category;
         $this->article;
    }
    public function save(){

            $this->validate();
                foreach ($this->images as $image) {
                    $image->store('images');
                }

                $this->article = Category::find($this->category)->articles()->create([
                    'title' => $this->title,
                    'body' => $this->body,
                    'price' => $this->price,
                    'user_id'=>Auth::user()->id,
                ]);
                
                if (count($this->images)) {
                    foreach ($this->images as $image) {
                        // $this->article->images()->create([
                        //     'path'=> $image->store('images','public')
                        // ]);
                        $newFileName = "articles/{$this->article->id}";
                        $newImage = $this->article->images()->create(['path'=> $image->store($newFileName, 'public')]);
                        
                        RemoveFaces::withChain([
                            new ResizeImage($newImage->path, 400, 300),
                            new GoogleVisionSafeSearch($newImage->id),
                            new GoogleVisionLabelImage($newImage->id),
                        ])->dispatch($newImage->id);
                    }

                    File::deleteDirectory(storage_path('/app/livewire-tmp'));
                }
                $this->cleanForm();
                return redirect()->route('create')->with('message', 'Articolo inserito correttamente, sarà pubblicato dopo la revisione');

         
    }
        

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function render()
    {
        return view('livewire.article-form');
    }


    
}
