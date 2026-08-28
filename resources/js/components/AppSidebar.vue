<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { GraduationCap, ShieldCheck, Terminal } from '@lucide/vue';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import adminRoutes from '@/routes/admin';
import courseRoutes from '@/routes/courses';
import playgroundRoutes from '@/routes/playground';
import type { NavItem } from '@/types';

const page = usePage();

const isAdmin = computed(() =>
    Boolean((page.props.auth?.user as { is_admin?: boolean })?.is_admin),
);

const mainNavItems: NavItem[] = [
    {
        title: 'Courses',
        href: courseRoutes.index.url(),
        icon: GraduationCap,
    },
    {
        title: 'Playground',
        href: playgroundRoutes.index.url(),
        icon: Terminal,
    },
];

const adminNavItems: NavItem[] = [
    {
        title: 'Admin Dashboard',
        href: adminRoutes.dashboard(),
        icon: ShieldCheck,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="courseRoutes.index.url()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />

            <div v-if="isAdmin" class="px-2 py-1">
                <div
                    class="flex items-center gap-2 px-2 py-1.5 text-xs font-medium tracking-wide text-gray-500 uppercase"
                >
                    <ShieldCheck class="h-3.5 w-3.5" />
                    Admin
                </div>
                <NavMain :items="adminNavItems" />
            </div>
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
