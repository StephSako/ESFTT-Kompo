ClassicEditor.create(
    document.querySelector('#editor'),
    {
        bold: {
            options: [
                {
                    element: 'b'
                }
            ]
        },
        heading: {
            options: [
                {
                    model: 'paragraph',
                    title: 'Paragraphe',
                    class: 'ck-heading_paragraph'
                },
                {
                    model: 'heading1',
                    title: 'Titre 1',
                    class: 'ck-heading_heading1',
                    view: 'h1'
                },
                {
                    model: 'heading2',
                    title: 'Titre 2',
                    class: 'ck-heading_heading2',
                    view: 'h2'
                },
                {
                    model: 'heading3',
                    title: 'Titre 3',
                    class: 'ck-heading_heading3',
                    view: 'h3'
                },
                {
                    model: 'heading4',
                    title: 'Titre 4',
                    class: 'ck-heading_heading4',
                    view: 'h4'
                },
                {
                    model: 'heading5',
                    title: 'Titre 5',
                    class: 'ck-heading_heading5',
                    view: 'h5'
                }
            ]
        },
        toolbar: ['heading', '|', 'undo', 'redo', '|', 'bold', 'italic', '|', 'numberedList', 'bulletedList', '|', 'blockquote', 'insertTable', 'link']
    }
).then(editor => document.querySelector("#settings-form form").addEventListener("submit", function (e) {
    e.preventDefault();
    this.querySelector('#settings-form #settings_informations').value = editor.getData();
    this.submit();
}));