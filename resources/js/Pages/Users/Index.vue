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
    name: "",
    username: "",
    email: "",
    password: "",
    role_name: "",
});
const form_put = useForm({
    method: "PUT",
    id: "",
    name: "",
    username: "",
    email: "",
    password: "",
    role_name: "",
});

const Field = [
    {
        name: "name",
        label: "Nama Lengkap",
        type: "text",
        required: true,
        model: "name",
        placeholder: "Masukkan Nama Lengkap",
    },
    {
        name: "username",
        label: "Username",
        type: "text",
        required: true,
        model: "username",
        placeholder: "Masukkan Username",
    },
    {
        name: "email",
        label: "Email",
        type: "email",
        required: true,
        model: "email",
        placeholder: "Masukkan Email",
    },
    {
        name: "password",
        label: "Password",
        type: "password",
        required: true,
        model: "password",
        placeholder: "Masukkan Password",
    },
    {
        name: "role_name",
        label: "Role",
        type: "select",
        required: true,
        model: "role_name",
        options: [
            { value: "admin", label: "Admin" },
            { value: "operator", label: "Operator" },
        ],
    },
    // name
];

const editModal = ref(false);
function openEditModal() {
    editModal.value = true;
}

// handleEdit
function handleEdit(item) {
    // console.log("Editing item:", item);
    form_put.id = item.id;
    form_put.name = item.name;
    form_put.username = item.username;
    form_put.email = item.email;
    form_put.role_name = item.role_name;
    openEditModal();
}

// submit
function submit() {
    form.post(route("users.store"), {
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
    form_put.put(route("users.update", { id: form_put.id }), {
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
        form.delete(route("users.destroy", { id: itemToDelete.value.id }), {
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
                Users Management
            </h2>
        </template>

        <!-- <h1 class="text-2xl font-bold mb-4"></h1> -->
        <button
            class="px-4 py-2 bg-blue-600 text-white rounded"
            @click="openModal"
        >
            Tambah User
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
                <div
                    v-for="(field, index) in Field"
                    :key="field.name"
                    class="mb-4"
                >
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        {{ field.label }}
                        <span v-if="field.required" class="text-red-500"
                            >*</span
                        >
                    </label>

                    <!-- Input text -->
                    <TextInput
                        v-if="field.type === 'text'"
                        v-model="form[field.model]"
                        :type="field.type"
                        :placeholder="field.placeholder"
                        :autofocus="index === 0"
                    />
                    <!-- Input number -->
                    <TextInput
                        v-else-if="field.type === 'number'"
                        v-model="form[field.model]"
                        :type="field.type"
                        :placeholder="field.placeholder"
                        :autofocus="index === 0"
                    />
                    <!-- email -->
                    <TextInput
                        v-else-if="field.type === 'email'"
                        v-model="form[field.model]"
                        :type="field.type"
                        :placeholder="field.placeholder"
                        :autofocus="index === 0"
                    />
                    <!-- password -->
                    <TextInput
                        v-else-if="field.type === 'password'"
                        v-model="form[field.model]"
                        :type="field.type"
                        :placeholder="field.placeholder"
                        :autofocus="index === 0"
                    />
                    <!-- Select input -->
                    <select
                        v-else-if="field.type === 'select'"
                        v-model="form[field.model]"
                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full"
                    >
                        <option disabled value="">
                            -- Pilih {{ field.label }} --
                        </option>
                        <option
                            v-for="option in field.options"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </option>
                    </select>
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
                <div
                    v-for="(field, index) in Field"
                    :key="field.name"
                    class="mb-4"
                >
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        {{ field.label }}
                        <span v-if="field.required" class="text-red-500"
                            >*</span
                        >
                    </label>

                    <!-- Input text -->
                    <TextInput
                        v-if="field.type === 'text'"
                        v-model="form_put[field.model]"
                        :type="field.type"
                        :placeholder="field.placeholder"
                        :autofocus="index === 0"
                    />
                    <!-- Input number -->
                    <TextInput
                        v-else-if="field.type === 'number'"
                        v-model="form_put[field.model]"
                        :type="field.type"
                        :placeholder="field.placeholder"
                        :autofocus="index === 0"
                    />
                    <!-- email -->
                    <TextInput
                        v-else-if="field.type === 'email'"
                        v-model="form_put[field.model]"
                        :type="field.type"
                        :placeholder="field.placeholder"
                        :autofocus="index === 0"
                    />
                    <!-- password -->
                    <TextInput
                        v-else-if="field.type === 'password'"
                        v-model="form_put[field.model]"
                        :type="field.type"
                        :placeholder="field.placeholder"
                        :autofocus="index === 0"
                    />
                    <!-- Select input -->
                    <select
                        v-else-if="field.type === 'select'"
                        v-model="form_put[field.model]"
                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full"
                    >
                        <option disabled value="">
                            -- Pilih {{ field.label }} --
                        </option>
                        <option
                            v-for="option in field.options"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </option>
                    </select>
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
