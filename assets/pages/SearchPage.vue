<script setup>
import {
    ref,
    watch,
    onMounted,
    computed,
} from 'vue';

const searchQuery = ref('');
const selectedSetCode = ref('');
const cards = ref([]);
const setCodes = ref([]);
const loadingCards = ref(false);
const currentPage = ref(1);
const cardsPerPage = 100;
const totalCards = ref(0);

async function fetchSetCodes() {
    try {
        const response = await fetch('/api/card/setCodes');
        if (!response.ok) throw new Error('Failed to fetch setCodes');
        setCodes.value = await response.json();
    } catch (error) {
        console.error(error);
    }
}

async function searchCards() {
    if (searchQuery.value.length < 3) return;

    loadingCards.value = true;
    try {
        const url = `/api/card/search?q=${searchQuery.value}&page=${currentPage.value}&limit=${cardsPerPage}${selectedSetCode.value ? `&setCode=${selectedSetCode.value}` : ''}`;
        const response = await fetch(url);
        if (!response.ok) throw new Error('Failed to fetch cards');
        const data = await response.json();
        cards.value = data.cards;
        totalCards.value = data.total;
    } catch (error) {
        console.error(error);
    } finally {
        loadingCards.value = false;
    }
}

const totalPages = computed(() => Math.ceil(totalCards.value / cardsPerPage));

function nextPage() {
    if (currentPage.value < totalPages.value) {
        currentPage.value += 1;
        searchCards();
    }
}

function prevPage() {
    if (currentPage.value > 1) {
        currentPage.value -= 1;
        searchCards();
    }
}

watch([searchQuery, selectedSetCode], () => {
    currentPage.value = 1; // Reset page when search query or set changes
    searchCards();
});

watch(currentPage, () => {
    searchCards();
});

onMounted(() => {
    fetchSetCodes();
});
</script>

<template>
    <div>
        <h1>Rechercher une Carte</h1>
        <label for="searchQuery">Rechercher une carte :</label>
        <input id="searchQuery" v-model="searchQuery" placeholder="Rechercher..." />
        <label for="selectedSetCode">Sélectionner un set :</label>
        <select id="selectedSetCode" v-model="selectedSetCode">
            <option value="">Tous les sets</option>
            <option v-for="setCode in setCodes" :key="setCode" :value="setCode">
                {{ setCode }}
            </option>
        </select>
    </div>
    <div class="card-list">
        <div v-if="loadingCards">Loading...</div>
        <div v-else>
            <div class="card" v-for="card in cards" :key="card.id">
                <router-link :to="{ name: 'get-card', params: { uuid: card.uuid } }">
                    {{ card.name }} - {{ card.uuid }}
                </router-link>
            </div>
        </div>
    </div>
    <div class="pagination">
        <button type="button" @click="prevPage" :disabled="currentPage === 1">Précédent</button>
        <span>Page {{ currentPage }} / {{ totalPages }}</span>
        <button type="button" @click="nextPage" :disabled="currentPage === totalPages">Suivant</button>
    </div>
</template>
