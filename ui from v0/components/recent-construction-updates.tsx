'use client'

import { makeStyles } from '@fluentui/react-components'

const useStyles = makeStyles({
  root: {
    backgroundColor: '#FFFFFF',
    borderRadius: '16px',
    padding: '32px',
    boxShadow: '0 1px 3px rgba(0, 0, 0, 0.06)',
    border: '1px solid rgba(0, 0, 0, 0.08)',
  },
  header: {
    marginBottom: '28px',
  },
  title: {
    fontSize: '20px',
    fontWeight: 700,
    color: '#2F6B3C',
    marginBottom: '6px',
  },
  subtitle: {
    fontSize: '14px',
    color: '#999999',
    fontWeight: 400,
  },
  timeline: {
    display: 'flex',
    flexDirection: 'column',
    gap: '20px',
  },
  timelineItem: {
    display: 'flex',
    gap: '20px',
  },
  iconContainer: {
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    width: '40px',
    height: '40px',
    borderRadius: '50%',
    backgroundColor: 'rgba(102, 187, 106, 0.15)',
    color: '#2F6B3C',
    fontSize: '18px',
    flexShrink: 0,
  },
  content: {
    flex: 1,
  },
  contentTitle: {
    fontSize: '14px',
    fontWeight: 600,
    color: '#333333',
    marginBottom: '4px',
  },
  contentDescription: {
    fontSize: '13px',
    color: '#666666',
    marginBottom: '6px',
    lineHeight: '1.4',
  },
  contentTime: {
    fontSize: '12px',
    color: '#999999',
    fontWeight: 500,
  },
})

const updates = [
  {
    icon: '📊',
    title: 'Phase Updated',
    description: 'Roofing phase progress updated to 65% completion by Site Manager',
    time: '2 hours ago',
  },
  {
    icon: '✓',
    title: 'Milestone Completed',
    description: 'Roof Decking Installation milestone marked as complete',
    time: '4 hours ago',
  },
  {
    icon: '👷',
    title: 'Worker Assignment',
    description: '4 additional workers assigned to waterproofing crew',
    time: '6 hours ago',
  },
  {
    icon: '🔍',
    title: 'Engineer Approval',
    description: 'Deck installation approved by structural engineer',
    time: '1 day ago',
  },
  {
    icon: '📋',
    title: 'Inspection Scheduled',
    description: 'Roof inspection scheduled for June 28, 2026',
    time: '2 days ago',
  },
  {
    icon: '⚠️',
    title: 'Schedule Alert',
    description: 'Weather delay pushed waterproofing start by 2 days',
    time: '3 days ago',
  },
]

export function RecentConstructionUpdates() {
  const styles = useStyles()

  return (
    <div className={styles.root}>
      <div className={styles.header}>
        <div className={styles.title}>Recent Construction Updates</div>
        <div className={styles.subtitle}>Latest activity and changes on the Roofing phase</div>
      </div>

      <div className={styles.timeline}>
        {updates.map((update, index) => (
          <div key={index} className={styles.timelineItem}>
            <div className={styles.iconContainer}>{update.icon}</div>
            <div className={styles.content}>
              <div className={styles.contentTitle}>{update.title}</div>
              <div className={styles.contentDescription}>{update.description}</div>
              <div className={styles.contentTime}>{update.time}</div>
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}
