<script setup>
import { ref } from "vue";
import DialogModal from "@/Components/DialogModal.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import ReusableTable from "@/Components/reusable/ReusableTable.vue";
import TextInput from "@/Components/TextInput.vue";
import { useForm } from "@inertiajs/vue3";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { usePage, router } from "@inertiajs/vue3";
import { computed } from "vue";
import Pagination from "@/Components/reusable/Pagination.vue";

const props = defineProps({
    items: Array,
    kategori: Array,
    filters: Object,
    pagination: Object,
    meta: Object,
});

const form = ref({
    kategori_id: props.filters.kategori_id || "",
    sub_kategori_id: props.filters.sub_kategori_id || "",
    search: props.filters.search || "",
});

function applyFilters() {
    router.get(
        "/barang-masuk",
        {
            kategori_id: form.value.kategori_id,
            sub_kategori_id: form.value.sub_kategori_id,
            search: form.value.search,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
}

// 💡 computed agar aman dan reusable
const subkategoris = computed(() =>
    (props.kategori ?? []).flatMap((k) => k.sub_kategoris ?? [])
);

// const Field = [
//     {
//         name: "kategori_id",
//         label: "Kategori",
//         type: "select",
//         required: true,
//         model: "kategori_id",
//         options: props.kategori.map((k) => ({
//             value: k.id,
//             label: k.nama_kategori,
//         })),
//         placeholder: "Pilih Kategori",
//     },
//     {
//         name: "nama_sub_kategori",
//         label: "Nama Sub Kategori",
//         type: "text",
//         required: true,
//         model: "nama_sub_kategori",
//         placeholder: "Masukkan Nama Sub Kategori",
//     },
//     {
//         name: "batas_harga",
//         label: "Batas Harga",
//         type: "number",
//         required: true,
//         model: "batas_harga",
//         placeholder: "Masukkan Batas Harga",
//     },
// ];

const editModal = ref(false);
function openEditModal() {
    editModal.value = true;
}

// handleEdit
function handleEdit(item) {
    // router.visit("barang-masuk.edit", { id: item.id });
    // route visit
    router.visit(route("barang-masuk.edit", { id: item.id }));
}

// submit
function submit() {
    // console.log(form);

    form.post(route("sub-kategori.store"), {
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
    form_put.put(route("sub-kategori.update", { id: form_put.id }), {
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
    // console.log("Deleting item:", item);
    // Open delete confirmation modal
    deleteModal.value = true;
    // Set the item to be deleted
    itemToDelete.value = item;
}
function confirmDelete() {
    if (itemToDelete.value) {
        // Perform the delete action
        form.delete(
            route("barang-masuk.destroy", { id: itemToDelete.value.id }),
            {
                onSuccess: () => {
                    closeModal();
                    itemToDelete.value = null; // Clear the item after deletion
                },
                onError: () => {
                    console.error("Error deleting item");
                },
            }
        );
    }
    closeModal();
}
</script>

<template>
    <AppLayout title="Dashboard">
        <!-- {{ kategori }} -->
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>

        <!-- <h1 class="text-2xl font-bold mb-4"></h1> -->
        <div class="flex flex-wrap justify-between items-center gap-4 mb-4">
            <!-- Tambah Barang -->
            <a href="/barang-masuk/create">
                <PrimaryButton class="mb-4">Tambah Barang Masuk</PrimaryButton>
            </a>

            <!-- Filter -->
            <div class="flex flex-wrap gap-4 items-center">
                <!-- Kategori -->
                <div class="flex items-center gap-2">
                    <label for="kategori" class="text-sm font-medium"
                        >Kategori:</label
                    >
                    <select
                        id="kategori"
                        v-model="form.kategori_id"
                        class="border border-gray-300 rounded-md p-2"
                    >
                        <option value="">-- Pilih Kategori --</option>
                        <option
                            v-for="kategori in props.kategori"
                            :key="kategori.id"
                            :value="kategori.id"
                        >
                            {{ kategori.nama_kategori }}
                        </option>
                    </select>
                </div>

                <!-- Sub Kategori -->
                <div class="flex items-center gap-2">
                    <label for="sub_kategori" class="text-sm font-medium"
                        >Sub Kategori:</label
                    >
                    <select
                        v-model="form.sub_kategori_id"
                        class="border border-gray-300 rounded-md p-2"
                    >
                        <option value="">-- Pilih Sub Kategori --</option>
                        <option
                            v-for="sub in subkategoris"
                            :key="sub.id"
                            :value="sub.id"
                        >
                            {{ sub.nama_sub_kategori }}
                        </option>
                    </select>
                </div>

                <!-- Search -->
                <input
                    v-model="form.search"
                    type="text"
                    placeholder="Search..."
                    class="border border-gray-300 rounded-md p-2"
                />

                <!-- Tombol Filter -->
                <PrimaryButton @click="applyFilters">Filter</PrimaryButton>
            </div>
        </div>
        <table class="min-w-full divide-y divide-gray-200 mt-4">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2">No</th>
                    <th class="px-4 py-2">Aksi</th>
                    <th class="px-4 py-2">Tanggal</th>
                    <th class="px-4 py-2">Asal Barang</th>
                    <th class="px-4 py-2">Penerima</th>
                    <th class="px-4 py-2">Unit</th>
                    <th class="px-4 py-2">Kode</th>
                    <th class="px-4 py-2">Nama</th>
                    <th class="px-4 py-2">Jumlah</th>
                    <th class="px-4 py-2">Harga</th>
                    <th class="px-4 py-2">Total</th>
                    <th class="px-4 py-2">Tanggal Expired</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <template v-for="(row, index) in items" :key="row.id">
                    <tr v-for="(item, itemIndex) in row.items" :key="itemIndex">
                        <!-- Kolom parent hanya di baris pertama -->
                        <td
                            v-if="itemIndex === 0"
                            :rowspan="row.items.length"
                            class="px-4 py-2 text-center align-top"
                        >
                            {{ index + 1 }}
                        </td>
                        <td
                            v-if="itemIndex === 0"
                            :rowspan="row.items.length"
                            class="px-4 py-2 text-center align-top"
                        >
                            <div class="flex flex-col gap-2">
                                <button
                                    class="px-2 py-1 bg-green-500 text-white rounded"
                                    @click="handleEdit(row)"
                                >
                                    Edit
                                </button>
                                <button
                                    class="px-2 py-1 bg-red-500 text-white rounded"
                                    @click="handleDelete(row)"
                                >
                                    Hapus
                                </button>
                            </div>
                        </td>
                        <td
                            v-if="itemIndex === 0"
                            :rowspan="row.items.length"
                            class="px-4 py-2 align-top"
                        >
                            {{ row.tanggal }}
                        </td>
                        <td
                            v-if="itemIndex === 0"
                            :rowspan="row.items.length"
                            class="px-4 py-2 align-top"
                        >
                            {{ row.asal_barang }}
                        </td>
                        <td
                            v-if="itemIndex === 0"
                            :rowspan="row.items.length"
                            class="px-4 py-2 align-top"
                        >
                            {{ row.penerima }}
                        </td>
                        <td
                            v-if="itemIndex === 0"
                            :rowspan="row.items.length"
                            class="px-4 py-2 align-top"
                        >
                            {{ row.unit }}
                        </td>

                        <!-- Kolom item -->
                        <td class="px-4 py-2">{{ item.kode }}</td>
                        <td class="px-4 py-2">{{ item.nama }}</td>
                        <td class="px-4 py-2">{{ item.jumlah }}</td>
                        <td class="px-4 py-2">
                            Rp {{ item.harga.toLocaleString() }}
                        </td>
                        <td class="px-4 py-2">
                            Rp {{ item.total.toLocaleString() }}
                        </td>
                        <td class="px-4 py-2">
                            {{
                                item.tanggal_expired
                                    ? item.tanggal_expired
                                    : "Tidak ada"
                            }}
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
        <Pagination :paginationLinks="pagination.links" :meta="meta" />
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
