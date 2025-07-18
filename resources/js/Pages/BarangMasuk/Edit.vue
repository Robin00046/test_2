<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { defineProps, onMounted, watch, computed, ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const props = defineProps({
    operators: Array,
    auth: Object,
    kategoris: Array,
    sub_kategoris: Array,
    errors: Object,
    barangMasuk: Object, // <--- Tambahkan ini untuk mode edit
});

const form = useForm({
    method: "put",
    user_id: props.barangMasuk.user_id ?? "",
    kategori_id: props.barangMasuk.kategori_id ?? "",
    sub_kategori_id: props.barangMasuk.sub_kategori_id ?? "",
    batas_harga: props.barangMasuk.batas_harga ?? "",
    asal_barang: props.barangMasuk.asal_barang ?? "",
    nomor_surat: props.barangMasuk.nomor_surat ?? "",
    tanggal_masuk: props.barangMasuk.tanggal_masuk ?? "",
    lampiran: null, // Tidak prefill file lama
    barang: props.barangMasuk.barang?.map((item) => ({
        id: item.id ?? null, // Tambahkan ID jika ada
        nama: item.nama_barang ?? "",
        harga: item.harga_barang ?? "",
        jumlah: item.jumlah_barang ?? "",
        satuan: item.satuan_barang ?? "Buah",
        total: item.total_harga ?? "",
        expired: item.tanggal_expired ?? "",
    })) ?? [
        {
            nama: "",
            harga: "",
            jumlah: "",
            satuan: "Buah",
            total: "",
            expired: "",
        },
    ],
});

onMounted(() => {
    if (props.auth.user.role_name !== "admin") {
        form.user_id = props.auth.user.id;
    }
});

watch(
    () => form.sub_kategori_id,
    (newVal) => {
        const selected = props.sub_kategoris.find((sub) => sub.id === newVal);
        form.batas_harga = selected?.batas_harga ?? "";
    }
);

const filteredSubKategoris = computed(() => {
    if (!form.kategori_id) return [];
    return props.sub_kategoris.filter(
        (sub) => sub.kategori_id === form.kategori_id
    );
});

const addItem = () => {
    form.barang.push({
        nama: "",
        harga: "",
        jumlah: "",
        satuan: "Buah",
        total: "",
        expired: "",
    });
};

const removeItem = (index) => {
    form.barang.splice(index, 1);
};

const submitForm = () => {
    // console.log("Submitting form with data:", form);

    form.put(route("barang-masuk.update", props.barangMasuk.id), {
        onSuccess: () => {
            console.log("Berhasil diperbarui!");
        },
        onError: (errors) => {
            console.error("Gagal update:", errors);
        },
    });
};
</script>

<template>
    <AppLayout title="Dashboard">
        <!-- {{ errors }} -->
        <h1 class="text-2xl font-bold mb-4">Tambah Barang Masuk</h1>
        <!-- {{ props.auth.user.role_name }} -->
        <!-- {{ props.operators }} -->
        <form @submit.prevent="submitForm" class="space-y-6">
            <!-- INFORMASI UMUM -->
            <div class="border p-4 rounded shadow bg-white">
                <h2 class="font-semibold text-gray-700 mb-4">INFORMASI UMUM</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm mb-1">Operator</label>

                        <!-- Jika user admin, tampilkan select -->
                        <select
                            v-if="props.auth.user.role_name === 'admin'"
                            v-model="form.user_id"
                            class="w-full border rounded px-3 py-2"
                        >
                            <option value="">-- Pilih Operator --</option>
                            <option
                                v-for="op in operators"
                                :key="op.id"
                                :value="op.id"
                            >
                                {{ op.name }}
                            </option>
                        </select>

                        <!-- Jika bukan admin, tampilkan teks nama user -->
                        <input
                            v-else
                            type="text"
                            class="w-full border rounded px-3 py-2 bg-gray-100"
                            :value="auth.user.name"
                            readonly
                        />
                    </div>

                    <!-- Kategori -->
                    <div>
                        <label class="block text-sm mb-1">Kategori</label>
                        <select
                            v-model="form.kategori_id"
                            class="w-full border rounded px-3 py-2"
                        >
                            <option value="">-- Pilih Kategori --</option>
                            <option
                                v-for="kategori in props.kategoris"
                                :key="kategori.id"
                                :value="kategori.id"
                            >
                                {{ kategori.nama_kategori }}
                            </option>
                        </select>
                    </div>

                    <!-- Sub Kategori -->
                    <div>
                        <label class="block text-sm mb-1">Sub Kategori</label>
                        <select
                            v-model="form.sub_kategori_id"
                            class="w-full border rounded px-3 py-2"
                        >
                            <option value="">-- Pilih Sub Kategori --</option>
                            <option
                                v-for="sub in filteredSubKategoris"
                                :key="sub.id"
                                :value="sub.id"
                            >
                                {{ sub.nama_sub_kategori }}
                            </option>
                        </select>
                        <div
                            class="text-red-500 text-xs mt-1"
                            v-if="errors.sub_kategori_id"
                        >
                            {{ errors.sub_kategori_id }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Batas Harga</label>
                        <input
                            v-model="form.batas_harga"
                            type="text"
                            class="w-full border rounded px-3 py-2 bg-gray-100"
                            readonly
                        />
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Tanggal Masuk</label>
                        <input
                            v-model="form.tanggal_masuk"
                            type="date"
                            class="w-full border rounded px-3 py-2"
                        />
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Asal Barang</label>
                        <input
                            v-model="form.asal_barang"
                            type="text"
                            class="w-full border rounded px-3 py-2"
                        />
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Nomor Surat</label>
                        <input
                            v-model="form.nomor_surat"
                            type="text"
                            class="w-full border rounded px-3 py-2"
                        />
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm mb-1">Lampiran</label>
                        <input
                            type="file"
                            class="w-full border rounded px-3 py-2"
                            @change="form.lampiran = $event.target.files[0]"
                        />
                    </div>
                </div>
            </div>

            <!-- INFORMASI BARANG -->
            <div class="border p-4 rounded shadow bg-white">
                <h2 class="font-semibold text-gray-700 mb-4">
                    INFORMASI BARANG
                </h2>
                <div
                    v-for="(item, index) in form.barang"
                    :key="index"
                    class="border p-3 rounded-md mb-4 bg-gray-50"
                >
                    <div class="grid grid-cols-6 gap-4">
                        <div class="col-span-1">
                            <label class="block text-sm mb-1"
                                >Nama Barang</label
                            >
                            <input
                                v-model="item.nama"
                                placeholder="Nama Barang"
                                class="w-full border rounded px-2 py-1"
                            />
                            <div
                                class="text-red-500 text-xs mt-1"
                                v-if="errors[`barang.${index}.nama`]"
                            >
                                {{ errors[`barang.${index}.nama`] }}
                            </div>
                        </div>

                        <div class="col-span-1">
                            <label class="block text-sm mb-1"
                                >Harga (Rp.)</label
                            >
                            <input
                                v-model="item.harga"
                                type="number"
                                placeholder="Harga"
                                class="w-full border rounded px-2 py-1"
                            />
                            <div
                                class="text-red-500 text-xs mt-1"
                                v-if="errors[`barang.${index}.harga`]"
                            >
                                {{ errors[`barang.${index}.harga`] }}
                            </div>
                        </div>

                        <div class="col-span-1">
                            <label class="block text-sm mb-1">Jumlah</label>
                            <input
                                v-model="item.jumlah"
                                type="number"
                                placeholder="Jumlah"
                                class="w-full border rounded px-2 py-1"
                            />
                            <div
                                class="text-red-500 text-xs mt-1"
                                v-if="errors[`barang.${index}.jumlah`]"
                            >
                                {{ errors[`barang.${index}.jumlah`] }}
                            </div>
                        </div>

                        <div class="col-span-1">
                            <label class="block text-sm mb-1">Satuan</label>
                            <select
                                v-model="item.satuan"
                                class="w-full border rounded px-2 py-1"
                            >
                                <option>Buah</option>
                                <option>Box</option>
                                <option>Rim</option>
                            </select>
                        </div>

                        <div class="col-span-1">
                            <label class="block text-sm mb-1">Total</label>
                            <input
                                v-model="item.total"
                                type="number"
                                placeholder="Total"
                                class="w-full border rounded px-2 py-1"
                            />
                            <div
                                class="text-red-500 text-xs mt-1"
                                v-if="errors[`barang.${index}.total`]"
                            >
                                {{ errors[`barang.${index}.total`] }}
                            </div>
                        </div>

                        <div class="col-span-1">
                            <label class="block text-sm mb-1"
                                >Tgl. Expired</label
                            >
                            <input
                                v-model="item.expired"
                                type="date"
                                class="w-full border rounded px-2 py-1"
                            />
                            <div
                                class="text-red-500 text-xs mt-1"
                                v-if="errors[`barang.${index}.expired`]"
                            >
                                {{ errors[`barang.${index}.expired`] }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-2 text-right" v-if="form.barang.length > 1">
                        <button
                            type="button"
                            class="text-red-600 text-sm"
                            @click="removeItem(index)"
                        >
                            ❌ Hapus Barang
                        </button>
                    </div>
                </div>

                <!-- Tombol tambah barang -->
                <button
                    type="button"
                    @click="addItem"
                    class="text-blue-600 text-sm mt-2 flex items-center"
                >
                    ➕ Tambah Barang
                </button>
            </div>

            <!-- BUTTON -->
            <div class="flex justify-between">
                <button type="button" class="bg-gray-300 px-4 py-2 rounded">
                    ← Kembali
                </button>
                <button
                    type="submit"
                    class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700"
                >
                    Simpan
                </button>
            </div>
        </form>
    </AppLayout>
</template>
