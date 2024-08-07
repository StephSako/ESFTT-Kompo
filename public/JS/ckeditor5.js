function AllowLinkTarget(editor) {
    editor.model.schema.extend('$text', {allowAttributes: 'editorVariableHighlighted'});
    editor.conversion.for('downcast').attributeToElement({
        model: 'editorVariableHighlighted',
        view: (attributeValue, {writer}) => {
            const linkElement = writer.createAttributeElement('span', {class: attributeValue}, {priority: 5});
            writer.setCustomProperty('link', true, linkElement);
            return linkElement;
        },
        converterPriority: 'low'
    });
    editor.conversion.for('upcast').attributeToAttribute({
        view: {
            name: 'span',
            key: 'class'
        },
        model: 'editorVariableHighlighted',
        converterPriority: 'low'
    });
}

BalloonEditor.create(
    document.querySelector('#editor'),
    {
        extraPlugins: [AllowLinkTarget],
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
        toolbar: ['heading', '|', 'undo', 'redo', '|', 'bold', 'italic', '|', 'numberedList', 'bulletedList', '|', 'blockquote', 'insertTable', 'link'],
        blockToolbar: ['heading', '|', 'undo', 'redo', '|', 'bold', 'italic', '|', 'numberedList', 'bulletedList', '|', 'blockquote', 'insertTable', 'link']
    }
).catch(error => {
    console.error(error);
}).then(editor => {
    const editorInstance = document.querySelector('.ck-editor__editable').ckeditorInstance;
    editorInstance.setData($('<div/>').html(ckeditor_content).text());

    document.querySelector("#settings-form form").addEventListener("submit", function (e) {
        e.preventDefault();
        this.querySelector('#settings-form #settings_content').value = editor.getData();
        this.submit();
    })
});