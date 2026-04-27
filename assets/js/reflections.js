// Guided reflections module JavaScript
// ------------------------------------------------------------
// This includes category prompt filtering, random prompt selection, new prompt generation etc.


document.addEventListener('DOMContentLoaded', function () {
    const getPromptBtn = document.getElementById('getPromptBtn');
    const newPromptBtn = document.getElementById('newPromptBtn');
    const emptyState = document.getElementById('reflectionEmptyState');
    const promptContent = document.getElementById('reflectionPromptContent');
    const responseCard = document.getElementById('reflectionResponseCard');
    const footerActions = document.getElementById('reflectionFooterActions');
    const promptQuestionText = document.getElementById('promptQuestionText');
    const promptCategoryBadge = document.getElementById('promptCategoryBadge');
    const promptDifficultyBadge = document.getElementById('promptDifficultyBadge');
    const promptIdInput = document.getElementById('promptIdInput');
    const promptCategoryInput = document.getElementById('promptCategoryInput');
    const promptTextInput = document.getElementById('promptTextInput');

    if (!getPromptBtn || !window.reflectionPrompts) {
        return;
    }

    let lastPromptId = null;

    function getFilteredPrompts() {
        const selectedCategory = window.reflectionSelectedCategory || 'All Topics';
        if (selectedCategory === 'All Topics') {
            return window.reflectionPrompts;
        }
        return window.reflectionPrompts.filter(item => item.category === selectedCategory);
    }

    function chooseRandomPrompt() {
        const prompts = getFilteredPrompts();
        if (!prompts.length) {
            alert('No prompts found for this category yet.');
            return;
        }

        let available = prompts;
        if (prompts.length > 1 && lastPromptId) {
            available = prompts.filter(item => Number(item.id) !== Number(lastPromptId));
            if (!available.length) {
                available = prompts;
            }
        }

        const randomIndex = Math.floor(Math.random() * available.length);
        const prompt = available[randomIndex];
        lastPromptId = prompt.id;

        promptQuestionText.textContent = '"' + prompt.prompt_text + '"';
        promptCategoryBadge.textContent = String(prompt.category || '').toUpperCase();
        promptDifficultyBadge.textContent = String(prompt.difficulty || 'Medium').toUpperCase();

        promptIdInput.value = prompt.id;
        promptCategoryInput.value = prompt.category;
        promptTextInput.value = prompt.prompt_text;

        emptyState.classList.add('d-none');
        promptContent.classList.remove('d-none');
        responseCard.classList.remove('d-none');
        footerActions.classList.remove('d-none');
    }

    getPromptBtn.addEventListener('click', chooseRandomPrompt);
    if (newPromptBtn) {
        newPromptBtn.addEventListener('click', chooseRandomPrompt);
    }
});
