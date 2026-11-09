<?php

namespace App\Http\Controllers;

use App\Models\AiKnowledge;
use Illuminate\Http\Request;

class AdminAiKnowledgeController extends Controller
{
    /**
     * Standard 6 categories for SIPAF Knowledge Base.
     */
    protected array $standardCategories = [
        'Profil Kecamatan',
        'Persyaratan Umum',
        'Pelayanan SIPAF',
        'Prosedur Pelayanan',
        'Informasi Umum',
        'FAQ (Frequently Asked Questions)',
    ];

    public function index(Request $request)
    {
        $search = $request->input('search');
        $category = $request->input('category');
        $query = AiKnowledge::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('question', 'like', "%{$search}%")
                  ->orWhere('keywords', 'like', "%{$search}%")
                  ->orWhere('answer', 'like', "%{$search}%");
            });
        }

        if ($category) {
            $query->where('category', $category);
        }

        $knowledges = $query->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $dbCategories = AiKnowledge::distinct()->pluck('category')->filter()->toArray();
        $allCategories = array_values(array_unique(array_merge($this->standardCategories, $dbCategories)));

        return view('admin.ai_knowledge.index', compact('knowledges', 'search', 'category', 'allCategories'));
    }

    public function create()
    {
        $categories = $this->standardCategories;
        return view('admin.ai_knowledge.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'question' => 'required|string',
            'answer' => 'required|string',
            'keywords' => 'nullable|string',
            'priority' => 'required|integer',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');

        AiKnowledge::create($data);

        return redirect()->route('admin.ai_knowledge.index')->with('success', 'Data knowledge base berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $knowledge = AiKnowledge::findOrFail($id);
        $categories = $this->standardCategories;
        if (!in_array($knowledge->category, $categories)) {
            $categories[] = $knowledge->category;
        }

        return view('admin.ai_knowledge.edit', compact('knowledge', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'category' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'question' => 'required|string',
            'answer' => 'required|string',
            'keywords' => 'nullable|string',
            'priority' => 'required|integer',
        ]);

        $knowledge = AiKnowledge::findOrFail($id);
        
        $data = $request->all();
        $data['is_active'] = $request->has('is_active');

        $knowledge->update($data);

        return redirect()->route('admin.ai_knowledge.index')->with('success', 'Data knowledge base berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $knowledge = AiKnowledge::findOrFail($id);
        $knowledge->delete();

        return redirect()->route('admin.ai_knowledge.index')->with('success', 'Data knowledge base berhasil dihapus.');
    }
    
    public function toggleActive($id)
    {
        $knowledge = AiKnowledge::findOrFail($id);
        $knowledge->is_active = !$knowledge->is_active;
        $knowledge->save();
        
        return redirect()->route('admin.ai_knowledge.index')->with('success', 'Status knowledge base berhasil diubah.');
    }
}
