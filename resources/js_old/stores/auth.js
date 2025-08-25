import { defineStore } from "pinia";
import { ref } from "vue";
import axios from "axios";
import { route as ziggyRoute } from 'ziggy-js'; // ✅ Ziggy route import

axios.defaults.withCredentials = true;
axios.defaults.baseURL = import.meta.env.VITE_API_BASE_URL;

export const useAuthStore = defineStore("auth", () => {
    const user = ref(null);
    const isAuthenticated = ref(false);
    const loading = ref(false);
    const isAuthChecked = ref(false);

    const login = async (credentials) => {
        loading.value = true;
        try {
            await axios.get("/sanctum/csrf-cookie");

            const response = await axios.post(
                ziggyRoute("api.login"),
                credentials
            );

            if (response.data.success) {
                user.value = response.data.user;
                isAuthenticated.value = true;
                return { success: true };
            }
        } catch (error) {
            return {
                success: false,
                message: error.response?.data?.message || "Login failed",
            };
        } finally {
            loading.value = false;
        }
    };

    const register = async (userData) => {
        loading.value = true;
        try {
            await axios.get("/sanctum/csrf-cookie");

            const response = await axios.post(
                ziggyRoute("api.register"),
                userData
            );

            if (response.data.success) {
                user.value = response.data.user;
                isAuthenticated.value = true;
                return { success: true };
            }
        } catch (error) {
            return {
                success: false,
                message: error.response?.data?.message || "Registration failed",
                errors: error.response?.data?.errors || {},
            };
        } finally {
            loading.value = false;
        }
    };

    const loginWithGoogle = async (googleToken) => {
        loading.value = true;
        try {
            await axios.get("/sanctum/csrf-cookie");

            const response = await axios.post(
                ziggyRoute("api.auth.google"),
                { token: googleToken }
            );

            if (response.data.success) {
                user.value = response.data.user;
                isAuthenticated.value = true;
                return { success: true };
            }
        } catch (error) {
            return {
                success: false,
                message: error.response?.data?.message || "Google login failed",
            };
        } finally {
            loading.value = false;
        }
    };

    const logout = async () => {
        try {
            await axios.post(ziggyRoute("api.logout"));
            user.value = null;
            isAuthenticated.value = false;
        } catch (error) {
            console.error("Logout error:", error);
        }
    };

    const checkAuth = async () => {
        try {
            const response = await axios.get(ziggyRoute("api.user.info")); // ✅ lấy từ route name
            if (response.data) {
                user.value = response.data;
                isAuthenticated.value = true;
            }
        } catch {
            user.value = null;
            isAuthenticated.value = false;
        } finally {
            isAuthChecked.value = true;
        }
    };

    return {
        user,
        isAuthenticated,
        loading,
        login,
        register,
        loginWithGoogle,
        logout,
        checkAuth,
    };
});
