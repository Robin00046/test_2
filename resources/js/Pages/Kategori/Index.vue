<script setup>
import { ref } from "vue";
import DialogModal from "@/Components/DialogModal.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import ReusableTable from "@/Components/reusable/ReusableTable.vue";
import TextInput from "@/Components/TextInput.vue";
import { useForm } from "@inertiajs/vue3";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const props = defineProps({
    columns: {
        type: Array,
        required: true,
    },
    ItemView: {
        type: Array,
        required: true,
    },
    items: {
        type: Object,
        required: true,
    },
    meta: {
        type: Object,
        required: true,
    },
});

const showModal = ref(false);

function openModal() {
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    editModal.value = false;
    deleteModal.value = false;
}

const form = useForm({
    kode_kategori: "",
    nama_kategori: "",
});
const form_put = useForm({
    method: "PUT",
    id: "",
    kode_kategori: "",
    nama_kategori: "",
});

const Field = [
    {
        name: "kode_kategori",
        label: "Kode Kategori",
        type: "text",
        required: true,
        model: "kode_kategori",
        placeholder: "Masukkan Kode Kategori",
    },
    {
        name: "nama_kategori",
        label: "Nama Kategori",
        type: "text",
        required: true,
        model: "nama_kategori",
        placeholder: "Masukkan Nama Kategori",
    },
];

const editModal = ref(false);
function openEditModal() {
    editModal.value = true;
}

// handleEdit
function handleEdit(item) {
    console.log("Editing item:", item);
    form_put.id = item.id;
    form_put.kode_kategori = item.kode_kategori;
    form_put.nama_kategori = item.nama_kategori;
    openEditModal();
}

// submit
function submit() {
    form.post(route("kategori.store"), {
        onSuccess: () => {
            closeModal();
            form.reset();
        },
        onError: () => {
            console.error("Error submitting form");
        },
    });
}

function EditSubmit() {
    form_put.put(route("kategori.update", { id: form_put.id }), {
        onSuccess: () => {
            closeModal();
            form.reset();
        },
        onError: () => {
            console.error("Error updating form");
        },
    });
}

const deleteModal = ref(false);
const itemToDelete = ref(null);

function handleDelete(item) {
    // Open delete confirmation modal
    deleteModal.value = true;
    // Set the item to be deleted
    itemToDelete.value = item;
}

function confirmDelete() {
    if (itemToDelete.value) {
        // Call the delete route with the item ID
        form.delete(route("kategori.destroy", { id: itemToDelete.value.id }), {
            onSuccess: () => {
                closeModal();
                itemToDelete.value = null; // Clear the item after deletion
            },
            onError: () => {
                console.error("Error deleting item");
            },
        });
    }
    closeModal();
    itemToDelete.value = null; // Clear the item after deletion
}
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>

        <!-- <h1 class="text-2xl font-bold mb-4"></h1> -->
        <button
            class="px-4 py-2 bg-blue-600 text-white rounded"
            @click="openModal"
        >
            Tambah Kategori
        </button>
        <ReusableTable
            :columns="columns"
            :data="ItemView"
            :actions="['edit', 'delete']"
            :pagination="items.links"
            :meta="meta"
            @edit="handleEdit"
            @delete="handleDelete"
            @page-change="handlePageChange"
        >
            <template #actions="{ item }">
                <button
                    @click="handleApprove(item)"
                    class="text-green-600 hover:text-green-900"
                    title="Approve"
                    v-if="item.status === 'pending'"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M5 13l4 4L19 7"
                        />
                    </svg>
                </button>
            </template>
        </ReusableTable>
    </AppLayout>

    <DialogModal :show="showModal" @close="closeModal">
        <template #title> Tambah Kategori</template>

        <template #content>
            <form @submit.prevent="closeModal">
                <div v-for="(field, index) in Field" :key="field.name">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        {{ field.label }}
                        <span v-if="field.required" class="text-red-500"
                            >*</span
                        >
                    </label>
                    <TextInput
                        v-model="form[field.model]"
                        :type="field.type"
                        :placeholder="field.placeholder"
                        :autofocus="index === 0"
                    ></TextInput>
                </div>
            </form>
        </template>

        <template #footer>
            <SecondaryButton @click="closeModal">Batal</SecondaryButton>
            <PrimaryButton @click="submit">Simpan</PrimaryButton>
        </template>
    </DialogModal>
    <DialogModal :show="editModal" @close="closeModal">
        <template #title> Edit Kategori</template>
        <template #content>
            <form @submit.prevent="closeModal">
                <div v-for="(field, index) in Field" :key="field.name">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        {{ field.label }}
                        <span v-if="field.required" class="text-red-500"
                            >*</span
                        >
                    </label>
                    <TextInput
                        v-model="form_put[field.model]"
                        :type="field.type"
                        :placeholder="field.placeholder"
                        :autofocus="index === 0"
                    ></TextInput>
                </div>
            </form>
        </template>

        <template #footer>
            <SecondaryButton @click="closeModal">Batal</SecondaryButton>
            <PrimaryButton @click="EditSubmit"> Simpan </PrimaryButton>
        </template>
    </DialogModal>

    <!-- Modal For DDelete -->
    <DialogModal :show="deleteModal" @close="closeModal">
        <template #title> Hapus Kategori</template>
        <template #content>
            <p>Apakah Anda yakin ingin menghapus kategori ini?</p>
        </template>

        <template #footer>
            <SecondaryButton @click="closeModal">Batal</SecondaryButton>
            <PrimaryButton @click="confirmDelete">Hapus</PrimaryButton>
        </template>
    </DialogModal>
</template>
