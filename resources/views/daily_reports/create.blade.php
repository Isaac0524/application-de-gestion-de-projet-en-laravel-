@extends('layout')
@section('content')
    <style>
        /* Responsive Create Daily Report Styles */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem;
        }

        .card {
            max-width: 820px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 0.5rem;
        }

        h2 {
            font-size: 1.875rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        form {
            margin: 0;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #374151;
        }

        input[type="text"],
        input[type="date"],
        textarea,
        select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 1rem;
            transition: border-color 0.2s;
            box-sizing: border-box;
        }

        input[type="text"]:focus,
        input[type="date"]:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        textarea {
            resize: vertical;
            min-height: 120px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-top: 1rem;
        }

        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            background-color: #3b82f6;
            color: white;
            border: none;
            border-radius: 0.375rem;
            cursor: pointer;
            transition: background-color 0.2s;
            text-align: center;
            text-decoration: none;
        }

        /* Style pour le champ d'upload */
        .file-upload-wrapper {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .file-upload-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1rem;
            background-color: #f8f9fa;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            cursor: pointer;
            transition: all 0.2s ease;
            color: #374151;
            font-weight: 600;
            font-size: 1rem;
        }

        .file-upload-label:hover {
            background-color: #e5e7eb;
            border-color: #3b82f6;
        }

        .file-upload-icon {
            width: 20px;
            height: 20px;
            margin-right: 0.5rem;
            stroke: #3b82f6;
        }

        .file-upload-input {
            display: none;
        }

        .file-name {
            margin-top: 0.5rem;
            font-size: 0.875rem;
            color: #6b7280;
            word-break: break-all;
        }

        /* Focus pour accessibilité */
        .file-upload-label:focus-within {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .btn:hover {
            background-color: #2563eb;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .card {
                margin: 1rem;
                padding: 1.5rem;
            }

            h2 {
                font-size: 1.5rem;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .card {
                margin: 0.5rem;
                padding: 1rem;
            }

            h2 {
                font-size: 1.25rem;
            }
        }
    </style>

    <div class="container">
        <div class="card">
            <h2>Ajouter un rapport journalier</h2>
        <form method="POST" action="{{ route('daily_reports.store') }}" enctype="multipart/form-data" data-form-confirmation>
                @csrf

                <div class="form-group">
                    <label>Description du travail effectué</label>
                    <textarea name="description" required placeholder="Décrivez votre travail du jour...">{{ old('description') }}</textarea>
                </div>

                <div class="form-row">
                    <div>
                        <label>Date du rapport</label>
                        <input type="date" name="report_date" value="{{ old('report_date', now()->format('Y-m-d')) }}"
                            required>
                    </div>
                    <div>
                        <label>Projet (optionnel)</label>
                        <select name="project_id">
                            <option value="">Sélectionner un projet</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}"
                                    {{ old('project_id') == $project->id ? 'selected' : '' }}>
                                    {{ $project->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="file-upload">Fichier joint (optionnel)</label>
                    <div class="file-upload-wrapper">
                        <label class="file-upload-label" for="file-upload">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                class="file-upload-icon">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16V4m0 0L3 8m4-4l4 4m6 4v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6m12 0l-3-3m0 0l-3 3" />
                            </svg>
                            Choisir un fichier
                        </label>
                        <input type="file" id="file-upload" class="file-upload-input" name="file"
                            accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.txt">
                        <div class="file-name" id="file-name">Aucun fichier sélectionné</div>
                    </div>
                </div>

                <div style="margin-top: 2rem; text-align: center;">
                    <button type="submit" class="btn">Enregistrer le rapport</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        document.getElementById('file-upload').addEventListener('change', function() {
            const fileName = this.files[0] ? this.files[0].name : 'Aucun fichier sélectionné';
            document.getElementById('file-name').textContent = fileName;
        });
    </script>
@endsection
